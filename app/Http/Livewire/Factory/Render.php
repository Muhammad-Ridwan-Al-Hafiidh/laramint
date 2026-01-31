<?php

namespace App\Http\Livewire\Factory;

use App\Models\Question;
use App\Utility\Question\QuestionFactory;
use Livewire\Component;
use App\Models\QuestionType;

class Render extends Component
{
    public ?int $questionTypeId = null;
    public string $component = '';
    public ?Question $question = null;
    public $quiz = null;
    public string $difficulty = 'medium';

    public function mount(): void
    {
        $firstType = QuestionType::first();
        if ($firstType && empty($this->question)) {
            $this->questionTypeId = $firstType->id;
            $this->getComponent($this->questionTypeId);
        }

        if (!empty($this->question)) {
            $this->questionTypeId = $this->question->question_type_id;
            $this->difficulty = $this->question->difficulty ?? 'medium';
            $this->getComponent($this->questionTypeId);
        }
    }

    private function getComponent($questionTypeId): void
    {
        if (!$questionTypeId) {
            $this->component = '';
            return;
        }
        $questionType = QuestionType::find($questionTypeId);
        if (!$questionType) {
            $this->component = '';
            return;
        }
        $this->component = (string) QuestionFactory::Build($questionType)->getCreateUpdateForm();
    }

    public function selectQuestionType(): void
    {
        $this->getComponent($this->questionTypeId);
    }

    public function render()
    {
        $questionTypes = QuestionType::all();
        return view('livewire.factory.render', compact('questionTypes'));
    }
}