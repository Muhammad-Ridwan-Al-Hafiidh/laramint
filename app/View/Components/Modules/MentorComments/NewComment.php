<?php

namespace App\View\Components\Modules\MentorComments;

use Illuminate\View\Component;
use Illuminate\Support\Facades\Auth;

class NewComment extends Component
{
    public int $userId;
    public string $activableType;
    public int $activableId;

    /**
     * Create a new component instance.
     */
    public function __construct(?int $userId, string $activableType, int $activableId)
    {
        $this->userId = $userId ?? Auth::id() ?? 0;
        $this->activableType = $activableType;
        $this->activableId = $activableId;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render()
    {
        return view('components.modules.mentor-comments.new-comment');
    }
}