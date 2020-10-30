<?php

namespace App\View\Components\Mail\Apparel;

use Illuminate\View\Component;

class Subject extends Component {
    /**
     * Apparel Email Subject
     *
     * @var string
     */
    public string $subject;

    /**
     * Create a new component instance.
     * @param string $subject
     *
     * @return void
     */
    public function __construct(string $subject) {
        //
        $this->subject = $subject;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render() {
        return view('components.mail.apparel.subject');
    }
}
