<?php

namespace App\UI;

class Layout
{
    private string $title;
    private string $description;

    public function __construct(string $title, string $description)
    {
        $this->title = $title;
        $this->description = $description;
    }

    public static function start(string $title, string $description = "Woodland RMS"): self
    {
        ob_start();
        return new self($title, $description);
    }

    public function end(): void
    {
        $content = ob_get_clean();
        require_once __DIR__ . '/../partials/header.partial.php';

        $title = $this->title;
        $description = $this->description;

        echo $content;

        require_once __DIR__ . '/../partials/footer.partial.php';
    }
}
