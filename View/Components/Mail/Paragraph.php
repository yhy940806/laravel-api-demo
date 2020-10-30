<?php

namespace App\View\Components\Mail;

use Illuminate\View\Component;

class Paragraph extends Component {
    /**
     * Text of paragraph
     *
     * @var string
     */
    public string $text;

    /**
     * Create a new component instance.
     * @param string $text
     *
     * @return void
     */
    public function __construct(string $text) {
        $this->text = $text;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render() {
        return view('components.mail.paragraph');
    }
}
