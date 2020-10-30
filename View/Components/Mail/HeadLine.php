<?php

namespace App\View\Components\Mail;

use Illuminate\View\Component;

class HeadLine extends Component {
    /**
     * Head line class
     *
     * @var string
     */
    public string $class;
    /**
     * The head line title
     *
     * @var string
     */
    public string $title;

    /**
     * Create a new component instance.
     * @param string $class
     * @param string $title
     * @return void
     */
    public function __construct(string $class, string $title) {
        $this->class = $class;
        $this->title = $title;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render() {
        return view('components.mail.head-line');
    }
}
