<?php
namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class Article
{
    public string $title = "dfbrbfv";

    /**
     * @Assert\NotBlank(message="Текст статьи обязателен!")
     */
    public string $content = "ervfvwefceraqvrevefbdfbg";
}
