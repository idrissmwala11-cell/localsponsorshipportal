<?php

namespace App\Http\Controllers;

use App\Models\Participant;
use App\Models\ParticipantSponsorship;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Throwable;

class ParticipantSponsorshipController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = ParticipantSponsorship::with('participant')
            ->visibleToUser($user);

        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                $q->where('funding_type', 'like', "%{$search}%")
                    ->orWhere('sponsorship_status', 'like', "%{$search}%")
                    ->orWhere('sponsored_by', 'like', "%{$search}%")
                    ->orWhere('sponsorship_category', 'like', "%{$search}%")
                    ->orWhereHas('participant', function ($participantQuery) use ($search) {
                        $participantQuery->where('account_name', 'like', "%{$search}%")
                            ->orWhere('local_participant_id', 'like', "%{$search}%");
                    });
            });
        }

        $sponsorships = $query->latest()->paginate(10)->withQueryString();

        return view('sponsorships.index', compact('sponsorships'));
    }

    public function create()
    {
        $participantsQuery = Participant::query()->visibleToUser(Auth::user());

        $participants = $participantsQuery
            ->orderBy('account_name')
            ->get();

        return view('sponsorships.create', compact('participants'));
    }

    public function store(Request $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $data = $request->validate([
                    'participant_id' => 'required|exists:participants,id',
                    'funding_type' => 'nullable|string|max:255',
                    'sponsorship_status' => 'nullable|string|max:255',
                    'sponsored_by' => 'nullable|string|max:255',
                    'sponsor_name' => 'nullable|string|max:255',
                    'sponsor_type' => 'nullable|string|max:255',
                    'sponsorship_type' => 'nullable|string|max:255',
                    'sponsor_physical_address' => 'nullable|string',
                    'sponsor_contact' => 'nullable|string|max:255',
                    'sponsorship_start_date' => 'nullable|date',
                    'sponsorship_category' => 'nullable|string|max:255',
                ]);

                if (empty($data['sponsor_name']) && !empty($data['sponsored_by'])) {
                    $data['sponsor_name'] = $data['sponsored_by'];
                }

                $participant = Participant::findOrFail($data['participant_id']);

                if (!Auth::user()->canAccessCenter($participant->center_id)) {
                    abort(403, 'Unauthorized access.');
                }

                $data['created_by_user_id'] = Auth::id();

                ParticipantSponsorship::create($data);
                $this->syncParticipantSummary($participant, $data);

                return redirect()->route('sponsorships.index')
                    ->with('success', 'Sponsorship information added successfully.');
            });
        } catch (Throwable $exception) {
            Log::error('Sponsorship create failed.', [
                'user_id' => Auth::id(),
                'message' => $exception->getMessage(),
            ]);

            return back()->withInput()->with('error', 'Sponsorship could not be saved. Please review the form and try again.');
        }
    }

    public function edit($id)
    {
        $sponsorship = ParticipantSponsorship::with('participant')->findOrFail($id);

        if (!Auth::user()->canAccessCenter(optional($sponsorship->participant)->center_id) || (Auth::user()->role === \App\Models\User::ROLE_USER && $sponsorship->created_by_user_id !== Auth::id())) {
            abort(403, 'Unauthorized access.');
        }

        $participantsQuery = Participant::query()->visibleToUser(Auth::user());

        $participants = $participantsQuery
            ->orderBy('account_name')
            ->get();

        return view('sponsorships.edit', compact('sponsorship', 'participants'));
    }

    public function update(Request $request, $id)
    {
        $sponsorship = ParticipantSponsorship::with('participant')->findOrFail($id);

        if (!Auth::user()->canAccessCenter(optional($sponsorship->participant)->center_id) || (Auth::user()->role === \App\Models\User::ROLE_USER && $sponsorship->created_by_user_id !== Auth::id())) {
            abort(403, 'Unauthorized access.');
        }

        try {
            return DB::transaction(function () use ($request, $sponsorship) {
                $data = $request->validate([
                    'participant_id' => 'required|exists:participants,id',
                    'funding_type' => 'nullable|string|max:255',
                    'sponsorship_status' => 'nullable|string|max:255',
                    'sponsored_by' => 'nullable|string|max:255',
                    'sponsor_name' => 'nullable|string|max:255',
                    'sponsor_type' => 'nullable|string|max:255',
                    'sponsorship_type' => 'nullable|string|max:255',
                    'sponsor_physical_address' => 'nullable|string',
                    'sponsor_contact' => 'nullable|string|max:255',
                    'sponsorship_start_date' => 'nullable|date',
                    'sponsorship_category' => 'nullable|string|max:255',
                ]);

                if (empty($data['sponsor_name']) && !empty($data['sponsored_by'])) {
                    $data['sponsor_name'] = $data['sponsored_by'];
                }

                $participant = Participant::findOrFail($data['participant_id']);

                if (!Auth::user()->canAccessCenter($participant->center_id)) {
                    abort(403, 'Unauthorized access.');
                }

                $sponsorship->update($data);
                $this->syncParticipantSummary($participant, $data);

                return redirect()->route('sponsorships.index')
                    ->with('success', 'Sponsorship information updated successfully.');
            });
        } catch (Throwable $exception) {
            Log::error('Sponsorship update failed.', [
                'sponsorship_id' => $sponsorship->id,
                'user_id' => Auth::id(),
                'message' => $exception->getMessage(),
            ]);

            return back()->withInput()->with('error', 'Sponsorship could not be updated. Please review the form and try again.');
        }
    }

    public function destroy($id)
    {
        $sponsorship = ParticipantSponsorship::with('participant')->findOrFail($id);

        if (!Auth::user()->canAccessCenter(optional($sponsorship->participant)->center_id) || (Auth::user()->role === \App\Models\User::ROLE_USER && $sponsorship->created_by_user_id !== Auth::id())) {
            abort(403, 'Unauthorized access.');
        }

        $sponsorship->delete();
        $this->refreshParticipantSummaryFromLatest(optional($sponsorship->participant));

        return redirect()->route('sponsorships.index')
            ->with('success', 'Sponsorship information deleted successfully.');
    }

    protected function syncParticipantSummary(Participant $participant, array $data): void
    {
        $participantUpdates = [];

        foreach ([
            'sponsorship_status',
            'funding_type',
            'sponsored_by',
            'sponsorship_start_date',
            'sponsorship_category',
            'sponsor_type',
            'sponsorship_type',
            'sponsor_physical_address',
            'sponsor_contact',
        ] as $column) {
            if (Schema::hasColumn('participants', $column) && filled($data[$column] ?? null)) {
                $participantUpdates[$column] = $data[$column];
            }
        }

        if (Schema::hasColumn('participants', 'sponsored_by') && filled($data['sponsor_name'] ?? null)) {
            $participantUpdates['sponsored_by'] = $data['sponsor_name'];
        }

        if (!empty($participantUpdates)) {
            $participant->update($participantUpdates);
        }
    }

    protected function refreshParticipantSummaryFromLatest(?Participant $participant): void
    {
        if (!$participant) {
            return;
        }

        $latest = $participant->sponsorships()->latest()->first();

        if (!$latest) {
            $participantUpdates = [];

            foreach ([
            'sponsorship_status',
            'funding_type',
            'sponsored_by',
            'sponsorship_start_date',
            'sponsorship_category',
            'sponsor_type',
            'sponsorship_type',
            'sponsor_physical_address',
            'sponsor_contact',
        ] as $column) {
                if (Schema::hasColumn('participants', $column)) {
                    $participantUpdates[$column] = null;
                }
            }

            if (!empty($participantUpdates)) {
                $participant->update($participantUpdates);
            }

            return;
        }

        $this->syncParticipantSummary($participant, [
            'sponsorship_status' => $latest->sponsorship_status,
            'funding_type' => $latest->funding_type,
            'sponsored_by' => $latest->sponsored_by,
            'sponsor_name' => $latest->sponsor_name,
            'sponsorship_start_date' => $latest->sponsorship_start_date,
            'sponsorship_category' => $latest->sponsorship_category,
            'sponsor_type' => $latest->sponsor_type,
            'sponsorship_type' => $latest->sponsorship_type,
            'sponsor_physical_address' => $latest->sponsor_physical_address,
            'sponsor_contact' => $latest->sponsor_contact,
        ]);
    }
}
