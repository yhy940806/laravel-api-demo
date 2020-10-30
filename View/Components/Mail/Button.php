<?php

namespace App\View\Components\Mail;

use Illuminate\View\Component;

class Button extends Component {
    /**
     * Button class
     *
     * @var string
     */
    public string $class;
    /**
     * Button text
     *
     * @var string
     */
    public string $text;
    /**
     * @var string|null
     */
    public ?string $link;

    /**
     * Create a new component instance.
     * @param string $class
     * @param string $text
     *
     * @param string|null $link
     */
    public function __construct(string $class, string $text, ?string $link = null) {
        //
        $this->class = $class;
        $this->text = $text;
        $this->link = $link;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render() {
        return view('components.mail.button');
    }
}
