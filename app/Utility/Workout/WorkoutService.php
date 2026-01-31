<?php

namespace App\Utility\Workout;

use App\Models\Participant;
use App\Models\Quiz;
use App\Models\Sessionable;
use App\Models\Term;
use App\Models\User;
use App\Models\Workout;
use App\Models\WorkoutQuizLog;

abstract class WorkoutService
{

    public static function checkExistWorkout(int $participant_id, Sessionable $sessionable, User $user): ?Workout
    {

        $workout = Workout::where('participant_id', $participant_id)
            ->where('sessionable_id', $sessionable->id)
            ->first();

        return $workout;
    }


    public static function WorkOutSyncForThisExcersice(Participant $participant, Sessionable $sessionable, User $user): Workout
    {

        $workout = self::checkExistWorkout($participant->id, $sessionable, $user);
        if (empty($workout)) {
            $workout = new Workout();
            $workout->participant_id = $participant->id;
            $workout->sessionable_id = $sessionable->id;
            $workout->date_first_view = now();
            $workout->is_completed = 0;
            $workout->score = 0;
            $workout->save();
            return $workout;
        }

        return $workout;
    }


    public static function setWorkOutQuizSyncForThisExcersice(Workout $workout, Quiz $quiz): ?array
    {

        if ($workout->WorkOutQuiz->count() > 0) {
            return [];
        }


        foreach ($quiz->Questions as $question) {
            WorkoutQuizLog::create([
                'workout_id' => $workout->id,
                'quiz_id' => $quiz->id,
                'question_id' => $question->id
            ]);
        }

        return null;
    }

    /**
     * Recompute workout score and completion flags from logs.
     */
    public static function recomputeScore(Workout $workout): int
    {
        $workoutQuiz = $workout->WorkOutQuiz;
        if (!$workoutQuiz || $workoutQuiz->count() === 0) {
            $workout->update([
                'score' => 0,
                'is_completed' => false,
                'is_mentor' => false,
                'date_get_score' => now(),
            ]);
            return 0;
        }

        $sumOfScore = 0;
        $is_completed = true;
        $is_mentor = false;
        foreach ($workoutQuiz as $question) {
            if ($is_mentor == false && $question->is_mentor) {
                $is_completed = false;
                $is_mentor = true;
            }
            $sumOfScore += (int)$question->score;
        }

        $score = (int)($sumOfScore / max(1, count($workoutQuiz)));

        $workout->update([
            'score' => $score,
            'is_completed' => $is_completed,
            'is_mentor' => $is_mentor,
            'date_get_score' => now(),
        ]);

        return $score;
    }
}