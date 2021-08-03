<?php

/**
  * Abstraksi.
  */
abstract class Page
{
    /**
     * @var Renderer
     */
    protected $renderer;

   /**
      * Abstraksi biasanya diinisialisasi dengan salah satu Implementasi
      * objek.
      */
    public function __construct(Renderer $renderer)
    {
        $this->renderer = $renderer;
    }

    /**
      * Pola Bridge memungkinkan penggantian objek Implementasi yang terlampir
      * secara dinamis.
      */
    public function changeRenderer(Renderer $renderer): void
    {
        $this->renderer = $renderer;
    }

    /**
      * Perilaku "tampilan" tetap abstrak karena hanya dapat diberikan oleh
      */
    abstract public function view(): string;
}

/**
  *  halaman sederhana.
  */
class SimplePage extends Page
{
    protected $title;
    protected $content;

    public function __construct(Renderer $renderer, string $title, string $content)
    {
        parent::__construct($renderer);
        $this->title = $title;
        $this->content = $content;
    }

    public function view(): string
    {
        return $this->renderer->renderParts([
            $this->renderer->renderHeader(),
            $this->renderer->renderTitle($this->title),
            $this->renderer->renderTextBlock($this->content),
            $this->renderer->renderFooter()
        ]);
    }
}

/**
  * halaman yang lebih kompleks.
  */
class ProductPage extends Page
{
    protected $product;

    public function __construct(Renderer $renderer, Product $product)
    {
        parent::__construct($renderer);
        $this->product = $product;
    }

    public function view(): string
    {
        return $this->renderer->renderParts([
            $this->renderer->renderHeader(),
            $this->renderer->renderTitle($this->product->getTitle()),
            $this->renderer->renderTextBlock($this->product->getDescription()),
            $this->renderer->renderImage($this->product->getImage()),
            $this->renderer->renderLink("/cart/add/" . $this->product->getId(), "Pesan!"),
            $this->renderer->renderFooter()
        ]);
    }
}

/**
  * Kelas pembantu untuk kelas ProductPage.
  */
class Product
{
    private $id, $title, $description, $image, $price;

    public function __construct(
        string $id,
        string $title,
        string $description,
        string $image,
        float $price
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->image = $image;
        $this->price = $price;
    }

    public function getId(): string { return $this->id; }

    public function getTitle(): string { return $this->title; }

    public function getDescription(): string { return $this->description; }

    public function getImage(): string { return $this->image; }

    public function getPrice(): float { return $this->price; }
}



interface Renderer
{
    public function renderTitle(string $title): string;

    public function renderTextBlock(string $text): string;

    public function renderImage(string $url): string;

    public function renderLink(string $url, string $title): string;

    public function renderHeader(): string;

    public function renderFooter(): string;

    public function renderParts(array $parts): string;
}

/**
  * halaman web sebagai HTML.
  */
class HTMLRenderer implements Renderer
{
    public function renderTitle(string $title): string
    {
        return "<h1>$title</h1>";
    }

    public function renderTextBlock(string $text): string
    {
        return "<div class='text'>$text</div>";
    }

    public function renderImage(string $url): string
    {
        return "<img src='$url'>";
    }

    public function renderLink(string $url, string $title): string
    {
        return "<a href='$url'>$title</a>";
    }

    public function renderHeader(): string
    {
        return "<html><body>";
    }

    public function renderFooter(): string
    {
        return "</body></html>";
    }

    public function renderParts(array $parts): string
    {
        return implode("\n", $parts);
    }
}

/**
  * halaman web sebagai string JSON.
  */
class JsonRenderer implements Renderer
{
    public function renderTitle(string $title): string
    {
        return '"title": "' . $title . '"';
    }

    public function renderTextBlock(string $text): string
    {
        return '"text": "' . $text . '"';
    }

    public function renderImage(string $url): string
    {
        return '"img": "' . $url . '"';
    }

    public function renderLink(string $url, string $title): string
    {
        return '"link": {"href": "' . $url . '", "title": "' . $title . '"}';
    }

    public function renderHeader(): string
    {
        return '';
    }

    public function renderFooter(): string
    {
        return '';
    }

    public function renderParts(array $parts): string
    {
        return "{\n" . implode(",\n", array_filter($parts)) . "\n}";
    }
}

/**
  * Kode klien biasanya hanya berhubungan dengan objek Abstraksi.
  */
function clientCode(Page $page)
{
    // ...

    echo $page->view();

    // ...
}

/**
  * Kode klien dapat dieksekusi dengan kombinasi apa pun yang telah dikonfigurasi sebelumnya dari
  * Abstraksi + Implementasi.
  */
$HTMLRenderer = new HTMLRenderer();
$JSONRenderer = new JsonRenderer();

$page = new SimplePage($HTMLRenderer, "Daftar Menu", "Selamat Datang!");
clientCode($page);
echo "\n\n";

/**
  * Abstraksi dapat mengubah Implementasi yang ditautkan saat runtime jika diperlukan.
  */



$product = new Product("123", "Soto Betawi",
    "Soto Betawi adalah salah satu kuliner tradisional Betawi yang sangat terkenal ...",
    "/images/sotobetawi.jpeg", 39.95);

$page = new ProductPage($HTMLRenderer, $product);

clientCode($page);
echo "\n\n";

$page->changeRenderer($JSONRenderer);

clientCode($page);