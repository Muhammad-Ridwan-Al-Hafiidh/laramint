<?php

namespace App\Http\Controllers\learn;

use App\Http\Controllers\Controller;
use App\Models\Participant;
use App\Models\Question;
use App\Models\Sessionable;
use App\Models\Workout;
use App\Utility\Modules\Tasks\TaskFactory;
use App\Utility\Question\QuestionFactory;
use App\Utility\Workout\WorkoutService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WorkoutController extends Controller
{
    public function restart(Workout $workout)
    {
        try { $workout->WorkOutQuiz()->delete(); } catch (\Throwable $e) { }
        $workout->update([
            'is_completed' => 0,
            'is_mentor' => 0,
            'score' => 0,
            'date_get_score' => null,
        ]);
        $sessionable = $workout->Sessionable;
        if ($sessionable && method_exists(WorkoutService::class, 'setWorkOutQuizSyncForThisExcersice')) {
            WorkoutService::setWorkOutQuizSyncForThisExcersice($workout, $sessionable->Model);
        }
        $participantId = $workout->participant_id ?? optional($workout->Participant)->id;
        $sessionableId = $workout->sessionable_id ?? optional($workout->Sessionable)->id;
        if ($participantId && $sessionableId) {
            return redirect()->route('taskLearner', ['participant' => $participantId, 'sessionable' => $sessionableId]);
        }
        return redirect()->back();
    }

    public function prepared(Participant $participant, Sessionable $sessionable)
    {
        WorkoutService::WorkOutSyncForThisExcersice($participant, $sessionable, Auth::user());
        return redirect(route('taskLearner', ['participant' => $participant, 'sessionable' => $sessionable]));
    }

    public function task(Participant $participant, Sessionable $sessionable)
    {
        $className = $sessionable->sessionable_type;
        $task = TaskFactory::Build($className);
        $task->set_user(Auth::user());
        return $task->Render($participant, $sessionable);
    }

    public function completedAndNext(Workout $workout)
    {
        $hasLogs = $workout->WorkOutQuiz && $workout->WorkOutQuiz->count() > 0;
        if ($hasLogs) {
            WorkoutService::recomputeScore($workout);
        } else {
            $workout->update(['is_completed' => 1, 'score' => 100, 'date_get_score' => now()]);
        }
        $participantId = $workout->participant_id ?? optional($workout->Participant)->id;
        $currentSessionable = $workout->Sessionable;
        $sessionId = optional($workout->Session)->id ?? optional($currentSessionable)->session_id;
        if ($sessionId && $currentSessionable) {
            $currentOrder = $currentSessionable->order ?? null;
            $next = Sessionable::where('session_id', $sessionId)
                ->when($currentOrder !== null, function ($q) use ($currentOrder) { $q->where('order', '>', $currentOrder); })
                ->orderBy('order')
                ->first();
            if ($next && $participantId) {
                return redirect()->route('taskLearner', ['participant' => $participantId, 'sessionable' => $next->id]);
            }
        }
        if ($participantId && $currentSessionable) {
            return redirect()->route('taskLearner', ['participant' => $participantId, 'sessionable' => $currentSessionable->id]);
        }
        return redirect()->back();
    }

    public function workout(Request $request)
    {
        $request->validate(['question_id' => 'required|int','workout_id' => 'required|int']);
        $question = Question::findorfail($request->question_id);
        $workout = Workout::findorfail($request->workout_id);
        $result =  QuestionFactory::Build($question->questionType)->workoutChecker($question, $workout, $request);
        return response()->json($result);
    }
}
