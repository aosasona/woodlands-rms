<?php

declare(strict_types=1);

namespace App\UI;

class Breadcrumb
{
    /**
     * @param array<int,array<string, string, bool>> $crumbs
     */
    public static function render(array $crumbs, string $class = "mt-4"): void
    {
        $links = "";

        foreach($crumbs as $crumb) {
            list($name, $path, $disabled) = $crumb;

            $crumb_class = $disabled ? " class=\"uk-disabled\"" : "";
            $crumb_link = match($path) {
                "" => "<span>%s</span>",
                default => "<a href=\"$path\">%s</a>"
            };

            $links .= sprintf("<li%s>$crumb_link</li>", $crumb_class, $name);
        }


        echo <<<HTML
        <nav aria-label="Breadcrumb" class="$class">
            <ul class="uk-breadcrumb">
              $links
            </ul>
        </nav>
      HTML;
    }

    /**
     * @return array<string,string, bool>
     */
    public static function crumb(string $name, string $path = "", bool $disabled = false): array
    {
        return [$name, $path, $disabled];
    }
}
