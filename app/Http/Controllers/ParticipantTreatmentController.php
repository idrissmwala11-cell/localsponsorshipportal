<?php

namespace App\Http\Controllers;

use App\Models\Participant;
use App\Models\ParticipantTreatment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class ParticipantTreatmentController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $participants = Participant::query()
            ->visibleToUser($user)
            ->orderBy('account_name')
            ->get();

        $treatments = ParticipantTreatment::query()
            ->with(['participant', 'creator'])
            ->visibleToUser($user)
            ->latest('treatment_date')
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        return view('treatments.index', [
            'participants' => $participants,
            'treatments' => $treatments,
        ]);
    }

    public function store(Request $request)
    {
        try {
            $user = $request->user();

            $participantIds = Participant::query()
                ->visibleToUser($user)
                ->pluck('id')
                ->all();

            $data = $request->validate([
                'participant_id' => ['required', 'integer'],
                'treatment' => ['nullable', 'string'],
                'treatment_date' => ['nullable', 'date'],
                'tested_diseases' => ['nullable', 'string'],
                'illness_type' => ['nullable', 'string', 'max:255'],
                'treatment_location' => ['nullable', 'string', 'max:255'],
                'treatment_cost' => ['nullable', 'numeric', 'min:0'],
                'health_comments' => ['nullable', 'string'],
            ]);

            if (!in_array((int) $data['participant_id'], $participantIds, true)) {
                return back()->withInput()->with('error', 'Selected participant is not available in your scope.');
            }

            $participant = Participant::query()->findOrFail($data['participant_id']);

            ParticipantTreatment::create([
                'participant_id' => $participant->id,
                'created_by_user_id' => $user->id,
                'center_id' => $participant->center_id,
                'treatment' => $data['treatment'] ?? null,
                'treatment_date' => $data['treatment_date'] ?? null,
                'tested_diseases' => $data['tested_diseases'] ?? null,
                'illness_type' => $data['illness_type'] ?? null,
                'treatment_location' => $data['treatment_location'] ?? null,
                'treatment_cost' => $data['treatment_cost'] ?? null,
                'health_comments' => $data['health_comments'] ?? null,
            ]);

            return redirect()
                ->route('treatments.index')
                ->with('success', 'Treatment record saved successfully.');
        } catch (Throwable $exception) {
            Log::error('Treatment record save failed.', [
                'user_id' => $request->user()?->id,
                'message' => $exception->getMessage(),
            ]);

            return back()->withInput()->with('error', 'Treatment record could not be saved. Please review the form and try again.');
        }
    }
}
