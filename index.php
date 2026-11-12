<?php
/**
 * Osteria del Borgo — Heritage Collection
 * Single-page native PHP prototype. All booking, ordering and newsletter
 * interactions are simulated in Vanilla JavaScript; no database is required.
 */
declare(strict_types=1);

$restaurant = [
    'name' => 'Osteria del Borgo',
    'since' => 1962,
    'address' => 'Borgo San Giuseppe, 12',
    'city' => '43121 Parma (PR)',
    'phone' => '+39 0521 000 000',
    'phone_href' => '+390521000000',
    'email' => 'info@osteriadelborgo.it',
];

/** Escape every dynamic value rendered into HTML. */
function e(string|int|float $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Inline SVG icon from the sprite declared at the top of the body. */
function icon(string $name, string $class = ''): string
{
    return '<svg class="icon ' . e($class) . '" aria-hidden="true" focusable="false"><use href="#i-' . e($name) . '"></use></svg>';
}

/** Italian price format, stored as a number to keep cart calculations exact. */
function price(float $amount): string
{
    return '€ ' . number_format($amount, 2, ',', '.');
}

$menu = [
    'antipasti' => [
        'label' => 'Antipasti', 'icon' => 'leaf',
        'note' => 'Il primo gesto di benvenuto: prodotti semplici, mani esperte.',
        'items' => [
            ['id' => 'antipasto-della-casa', 'name' => 'Antipasto della Casa', 'desc' => 'Prosciutto di Parma, Parmigiano Reggiano, olive, verdure grigliate e focaccia dal forno a legna.', 'price' => 18.00, 'image' => 'antipasto', 'badge' => true, 'meta' => 'Per iniziare, come una volta'],
            ['id' => 'burrata-pugliese', 'name' => 'Burrata Pugliese', 'desc' => 'Cuore cremoso, pomodorini confit, basilico fresco e olio extravergine ligure.', 'price' => 14.00, 'image' => 'burrata', 'badge' => false, 'meta' => 'Vegetariano'],
            ['id' => 'bruschetta-al-basilico', 'name' => 'Bruschetta al Basilico', 'desc' => 'Pane casereccio abbrustolito, pomodoro maturato al sole, aglio dolce e basilico.', 'price' => 9.00, 'image' => 'bruschetta', 'badge' => false, 'meta' => 'Vegetariano'],
        ],
    ],
    'paste' => [
        'label' => 'Paste Fresche', 'icon' => 'wheat',
        'note' => 'Tirate a mano ogni mattina, come ci ha insegnato la nonna.',
        'items' => [
            ['id' => 'tagliatelle-al-ragu', 'name' => 'Tagliatelle al Ragù', 'desc' => 'Sfoglia all’uovo e ragù di manzo cotto lentamente per sei ore, Parmigiano Reggiano 24 mesi.', 'price' => 16.00, 'image' => 'tagliatelle', 'badge' => true, 'meta' => 'La ricetta del 1962'],
            ['id' => 'tortelli-erbetta', 'name' => 'Tortelli d’Erbetta', 'desc' => 'Pasta fresca ripiena di ricotta, bietole e noce moscata, burro fuso e salvia croccante.', 'price' => 15.00, 'image' => 'tortelli', 'badge' => true, 'meta' => 'Specialità parmigiana'],
            ['id' => 'anolini-in-brodo', 'name' => 'Anolini in Brodo', 'desc' => 'Piccoli scrigni di pasta ripiena in brodo di cappone chiarificato, profumo di domenica.', 'price' => 14.00, 'image' => 'anolini', 'badge' => false, 'meta' => 'Il calore di casa'],
        ],
    ],
    'risotti' => [
        'label' => 'Risotti', 'icon' => 'wheat',
        'note' => 'Riso Carnaroli, mantecatura lenta e il rispetto che il tempo merita.',
        'items' => [
            ['id' => 'risotto-ai-porcini', 'name' => 'Risotto ai Porcini', 'desc' => 'Carnaroli mantecato al Parmigiano, funghi porcini trifolati e timo fresco.', 'price' => 18.00, 'image' => 'risotto', 'badge' => true, 'meta' => 'Vegetariano'],
            ['id' => 'risotto-allo-zafferano', 'name' => 'Risotto allo Zafferano', 'desc' => 'Zafferano in pistilli, finferli dorati, brodo vegetale e burro di latteria mantecato all’onda.', 'price' => 16.00, 'image' => 'risotto-zafferano', 'badge' => false, 'meta' => 'Vegetariano'],
        ],
    ],
    'secondi' => [
        'label' => 'Secondi', 'icon' => 'flame',
        'note' => 'Cotture lente, brace viva e sapori che non hanno fretta.',
        'items' => [
            ['id' => 'tagliata-di-manzo', 'name' => 'Tagliata di Manzo', 'desc' => 'Controfiletto alla brace, rucola selvatica, scaglie di Parmigiano e riduzione al balsamico.', 'price' => 26.00, 'image' => 'tagliata', 'badge' => true, 'meta' => 'Cottura alla brace'],
            ['id' => 'brasato-al-lambrusco', 'name' => 'Brasato al Lambrusco', 'desc' => 'Manzo brasato lentamente nel Lambrusco, risotto cremoso al Parmigiano e fondo di cottura ristretto.', 'price' => 22.00, 'image' => 'brasato', 'badge' => false, 'meta' => 'Cottura di otto ore'],
        ],
    ],
    'dolci' => [
        'label' => 'Dolci', 'icon' => 'spark',
        'note' => 'Il finale non è una conclusione: è un altro motivo per restare.',
        'items' => [
            ['id' => 'tiramisu-della-casa', 'name' => 'Tiramisù della Casa', 'desc' => 'Savoiardi inzuppati nel caffè, crema al mascarpone e cacao amaro. Preparato ogni giorno.', 'price' => 8.00, 'image' => 'tiramisu', 'badge' => true, 'meta' => 'Il nostro dolce più amato'],
            ['id' => 'panna-cotta-agli-agrumi', 'name' => 'Panna Cotta agli Agrumi', 'desc' => 'Vaniglia Bourbon, panna fresca e una delicata salsa agli agrumi, profumata di limone.', 'price' => 7.00, 'image' => 'pannacotta', 'badge' => false, 'meta' => 'Senza glutine'],
        ],
    ],
    'vini' => [
        'label' => 'Vini', 'icon' => 'wine',
        'note' => 'Piccoli produttori, grandi storie. Al calice o in bottiglia.',
        'items' => [
            ['id' => 'lambrusco-del-borgo', 'name' => 'Lambrusco del Borgo', 'desc' => 'Rosso vivo e leggermente frizzante, note di ciliegia e violetta. Emilia-Romagna DOC.', 'price' => 24.00, 'image' => 'wine-red', 'badge' => true, 'meta' => '75 cl · Servito fresco'],
            ['id' => 'malvasia-delle-colline', 'name' => 'Malvasia delle Colline', 'desc' => 'Bianco fermo e aromatico, fiori bianchi e mandorla fresca. Colli Piacentini DOC.', 'price' => 26.00, 'image' => 'wine-white', 'badge' => false, 'meta' => '75 cl · Calice € 6'],
            ['id' => 'rosso-della-riserva', 'name' => 'Rosso della Riserva', 'desc' => 'Blend di uve autoctone affinato in rovere, struttura elegante e finale lungo. Gutturnio Riserva.', 'price' => 32.00, 'image' => 'cantina-barrels', 'badge' => false, 'meta' => '75 cl · Annata selezionata'],
        ],
    ],
];

$gallery = [
    ['image' => 'hero', 'title' => 'La sala grande', 'caption' => 'Dove le serate diventano ricordi.'],
    ['image' => 'pasta-making', 'title' => 'Il gesto che si ripete', 'caption' => 'Farina, uova e memoria.'],
    ['image' => 'parma', 'title' => 'Le strade di casa', 'caption' => 'Parma, la nostra prima ispirazione.'],
    ['image' => 'cantina-barrels', 'title' => 'Il tempo, custodito', 'caption' => 'Ogni bottiglia aspetta la sua tavola.'],
    ['image' => 'tortelli', 'title' => 'Fatto a mano, ogni giorno', 'caption' => 'La pazienza è il nostro ingrediente segreto.'],
    ['image' => 'candle-table', 'title' => 'La luce della sera', 'caption' => 'Un tavolo apparecchiato è una promessa.'],
];

$reviews = [
    ['name' => 'Chiara R.', 'detail' => 'Parma · Cena in famiglia', 'avatar' => 'avatar-chiara', 'text' => 'Appena varcata la soglia ci siamo sentiti a casa. I tortelli d’erbetta hanno il sapore della domenica dalla nonna. Torneremo, senza dubbio.'],
    ['name' => 'Marco B.', 'detail' => 'Milano · Cena di lavoro', 'avatar' => 'avatar-marco', 'text' => 'Un posto raro: elegante ma mai formale. Il brasato al Lambrusco è straordinario e il servizio ha il ritmo giusto, quello delle cose fatte con cura.'],
    ['name' => 'Elena e Davide', 'detail' => 'Bologna · Anniversario', 'avatar' => 'avatar-elena', 'text' => 'Candele, legno antico, un calice di Malvasia. Abbiamo festeggiato dodici anni insieme e ci è sembrato di essere gli unici ospiti della sala.'],
    ['name' => 'Luca P.', 'detail' => 'Torino · Pranzo tra amici', 'avatar' => 'avatar-marco', 'text' => 'La tagliatella al ragù racconta davvero questo territorio. Porzioni giuste, prezzi onesti e un sorriso sincero al momento del conto. Grazie.'],
];

$faqs = [
    ['q' => 'È necessario prenotare?', 'a' => 'Ve lo consigliamo, soprattutto per il venerdì sera, il sabato e la domenica a pranzo. Potete richiedere un tavolo dal modulo qui sopra: la conferma è sempre personale, come piace a noi.'],
    ['q' => 'Posso segnalare allergie o intolleranze?', 'a' => 'Certamente. Indicatecelo nelle note della prenotazione o chiamateci prima della visita. La cucina valuterà insieme a voi le alternative possibili, con attenzione alle contaminazioni crociate.'],
    ['q' => 'Avete piatti vegetariani o senza glutine?', 'a' => 'Sì. Il menù propone diverse verdure, paste fresche vegetariane e dolci senza glutine. La disponibilità può variare con la stagione: chiedete sempre al nostro staff.'],
    ['q' => 'Possiamo organizzare una cena di gruppo?', 'a' => 'Fino a otto persone potete utilizzare il modulo online. Per tavolate più grandi, ricorrenze o cene private, chiamateci: costruiremo insieme un menù dedicato.'],
    ['q' => 'Come funzionano consegna e asporto?', 'a' => 'Scegliete i piatti, indicate l’indirizzo per la consegna o l’orario per il ritiro. In questo prototipo l’ordine è simulato: nessun pagamento viene richiesto e nessuna spedizione viene avviata.'],
    ['q' => 'La terrazza è sempre disponibile?', 'a' => 'La terrazza è aperta da maggio a settembre, compatibilmente con il meteo. In caso di pioggia vi accogliamo volentieri in sala, senza farvi perdere il vostro posto.'],
];

$catalog = [];
foreach ($menu as $category) {
    foreach ($category['items'] as $item) {
        if ($item['orderable'] ?? true) {
            $catalog[$item['id']] = ['id' => $item['id'], 'name' => $item['name'], 'price' => $item['price'], 'image' => $item['image']];
        }
    }
}

$today = new DateTimeImmutable('today', new DateTimeZone('Europe/Rome'));
$maxDate = $today->modify('+90 days');
$year = (int) $today->format('Y');

/** Render a menu dish; the same data drives the cart and quick-order cards. */
function render_dish(array $dish, bool $compact = false): void
{
    $orderable = $dish['orderable'] ?? true;
    ?>
    <article class="dish-card<?= $compact ? ' dish-card--compact' : '' ?>" data-reveal data-dish-id="<?= e($dish['id']) ?>">
        <div class="dish-media">
            <img src="assets/images/<?= e($dish['image']) ?>.webp" alt="<?= e($dish['name']) ?>" width="900" height="672" loading="lazy" decoding="async">
            <?php if (!empty($dish['badge'])): ?><span class="dish-badge"><?= icon('spark') ?> Specialità della Casa</span><?php endif; ?>
            <?php if ($orderable): ?>
                <button class="dish-add" type="button" data-add="<?= e($dish['id']) ?>" aria-label="Aggiungi <?= e($dish['name']) ?> al carrello"><?= icon('plus') ?></button>
            <?php endif; ?>
        </div>
        <div class="dish-body">
            <div class="dish-title-row"><h3><?= e($dish['name']) ?></h3><span class="dish-price"><?= e(price($dish['price'])) ?></span></div>
            <p><?= e($dish['desc']) ?></p>
            <span class="dish-meta"><?= icon($compact ? 'clock' : 'leaf') ?><?= e($dish['meta']) ?></span>
        </div>
    </article>
    <?php
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Osteria del Borgo · Cucina emiliana nel cuore di Parma</title>
    <meta name="description" content="Dal 1962, pasta fresca, ricette emiliane e una cantina che racconta il territorio. Osteria del Borgo: tradizione italiana nel cuore di Parma.">
    <meta name="theme-color" content="#171310">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="it_IT">
    <meta property="og:title" content="Osteria del Borgo · La tradizione italiana dal cuore di Parma">
    <meta property="og:description" content="Una tavola, una famiglia, una storia. Cucina emiliana, pasta fatta a mano e ospitalità autentica.">
    <meta property="og:image" content="assets/images/hero.webp">
    <link rel="icon" href="assets/favicon.svg" type="image/svg+xml">
    <link rel="preload" href="assets/images/hero.webp" as="image" fetchpriority="high">
    <link rel="preload" href="assets/fonts/cormorant-garamond-latin-500-normal.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="assets/fonts/manrope-latin-400-normal.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="assets/vendor/lenis.css">
    <link rel="stylesheet" href="assets/vendor/swiper-bundle.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <noscript><style>.menu-panel[hidden]{display:block!important}.menu-tabs,.dish-add,[data-cart-open],.nav-toggle,.form,.newsletter-form{display:none}</style></noscript>
</head>
<body>
<a class="skip-link" href="#main">Vai al contenuto</a>

<!-- Icon sprite: a single local request, no icon-font dependency. -->
<svg class="svg-sprite" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
    <symbol id="i-arrow-right" viewBox="0 0 24 24"><path d="M4 12h15m-6-6 6 6-6 6"/></symbol>
    <symbol id="i-arrow-left" viewBox="0 0 24 24"><path d="M20 12H5m6-6-6 6 6 6"/></symbol>
    <symbol id="i-arrow-down" viewBox="0 0 24 24"><path d="M12 4v15m-6-6 6 6 6-6"/></symbol>
    <symbol id="i-plus" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></symbol>
    <symbol id="i-minus" viewBox="0 0 24 24"><path d="M5 12h14"/></symbol>
    <symbol id="i-close" viewBox="0 0 24 24"><path d="m6 6 12 12M18 6 6 18"/></symbol>
    <symbol id="i-menu" viewBox="0 0 24 24"><path d="M4 7h16M4 12h16M4 17h16"/></symbol>
    <symbol id="i-bag" viewBox="0 0 24 24"><path d="M5 8h14l-1 12H6L5 8Z"/><path d="M9 8V6a3 3 0 0 1 6 0v2"/></symbol>
    <symbol id="i-phone" viewBox="0 0 24 24"><path d="M7 3H4a1 1 0 0 0-1 1c0 9.4 7.6 17 17 17a1 1 0 0 0 1-1v-3l-4-2-2 2a13 13 0 0 1-6-6l2-2-2-4Z"/></symbol>
    <symbol id="i-mail" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="1"/><path d="m3 7 9 6 9-6"/></symbol>
    <symbol id="i-pin" viewBox="0 0 24 24"><path d="M12 21s7-6.1 7-11a7 7 0 1 0-14 0c0 4.9 7 11 7 11Z"/><circle cx="12" cy="10" r="2.5"/></symbol>
    <symbol id="i-clock" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></symbol>
    <symbol id="i-calendar" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="16" rx="1"/><path d="M7 3v4m10-4v4M3 10h18"/></symbol>
    <symbol id="i-users" viewBox="0 0 24 24"><circle cx="9" cy="8" r="3"/><path d="M3 20v-2a6 6 0 0 1 12 0v2m1-14a3 3 0 0 1 0 6m2 8v-2a5 5 0 0 0-3-4"/></symbol>
    <symbol id="i-leaf" viewBox="0 0 24 24"><path d="M20 4C10 4 4 9 4 15a5 5 0 0 0 5 5c6 0 11-6 11-16Z"/><path d="M4 20 15 9"/></symbol>
    <symbol id="i-wheat" viewBox="0 0 24 24"><path d="M12 21V7m0 4c-3 0-5-2-5-5 3 0 5 2 5 5Zm0 4c-3 0-5-2-5-5 3 0 5 2 5 5Zm0 4c-3 0-5-2-5-5 3 0 5 2 5 5Zm0-8c3 0 5-2 5-5-3 0-5 2-5 5Zm0 4c3 0 5-2 5-5-3 0-5 2-5 5Zm0 4c3 0 5-2 5-5-3 0-5 2-5 5ZM12 7c0-3 1-5 0-5s0 2 0 5Z"/></symbol>
    <symbol id="i-wine" viewBox="0 0 24 24"><path d="M7 3h10l1 6a6 6 0 0 1-12 0l1-6Z"/><path d="M6 8h12M12 15v6m-4 0h8"/></symbol>
    <symbol id="i-flame" viewBox="0 0 24 24"><path d="M12 3c1 4-4 5-4 9a4 4 0 0 0 8 0c0-2-1-3-1-3 3 2 4 4 4 7a7 7 0 0 1-14 0c0-6 5-8 7-13Z"/></symbol>
    <symbol id="i-star" viewBox="0 0 24 24"><path d="m12 3 2.8 5.7 6.2.9-4.5 4.4 1.1 6.2L12 17.3l-5.6 2.9 1.1-6.2L3 9.6l6.2-.9L12 3Z"/></symbol>
    <symbol id="i-check" viewBox="0 0 24 24"><path d="m5 12 5 5L20 7"/></symbol>
    <symbol id="i-chevron" viewBox="0 0 24 24"><path d="m6 9 6 6 6-6"/></symbol>
    <symbol id="i-truck" viewBox="0 0 24 24"><path d="M3 6h11v10H3V6Zm11 4h4l3 3v3h-7"/><circle cx="7" cy="18" r="2"/><circle cx="17" cy="18" r="2"/></symbol>
    <symbol id="i-takeaway" viewBox="0 0 24 24"><path d="M5 8h14l-2 13H7L5 8Zm2 0 2-5h6l2 5M9 12h6"/></symbol>
    <symbol id="i-instagram" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17" cy="7" r="1" fill="currentColor" stroke="none"/></symbol>
    <symbol id="i-facebook" viewBox="0 0 24 24"><path d="M14 21v-8h3l.5-4H14V7a2 2 0 0 1 2-2h2V2h-3a5 5 0 0 0-5 5v2H7v4h3v8"/></symbol>
    <symbol id="i-zoom" viewBox="0 0 24 24"><circle cx="10" cy="10" r="6"/><path d="m15 15 6 6M8 10h4m-2-2v4"/></symbol>
    <symbol id="i-spark" viewBox="0 0 24 24"><path d="M12 3v4m0 10v4M3 12h4m10 0h4M6 6l3 3m6 6 3 3M18 6l-3 3m-6 6-3 3"/></symbol>
</svg>

<header class="site-header" id="site-header">
    <div class="header-inner">
        <nav class="nav nav--left" aria-label="Navigazione principale">
            <a href="#home" class="nav-link is-active">Home</a>
            <a href="#storia" class="nav-link">La Nostra Storia</a>
            <a href="#menu" class="nav-link">Menù</a>
            <a href="#galleria" class="nav-link">Galleria</a>
        </nav>
        <a class="brand" href="#home" aria-label="Osteria del Borgo, torna all’inizio">
            <span class="brand-mark" aria-hidden="true"><svg viewBox="0 0 44 52"><path d="M6 48V22a16 16 0 0 1 32 0v26M11 48V23a11 11 0 0 1 22 0v25M22 42V18m0 8c-5 0-7-3-7-6 4 0 7 2 7 6Zm0 7c-5 0-7-3-7-6 4 0 7 2 7 6Zm0-7c5 0 7-3 7-6-4 0-7 2-7 6Zm0 7c5 0 7-3 7-6-4 0-7 2-7 6Z"/></svg></span>
            <span class="brand-type"><span class="brand-osteria">Osteria</span><span class="brand-name">del Borgo</span><span class="brand-city">Parma · dal <?= e($restaurant['since']) ?></span></span>
        </a>
        <div class="header-right">
            <nav class="nav nav--right" aria-label="Navigazione servizi">
                <a href="#prenotazione" class="nav-link">Prenotazione</a>
                <a href="#consegna" class="nav-link">Consegna</a>
                <a href="#contatti" class="nav-link">Contatti</a>
            </nav>
            <a href="#prenotazione" class="btn btn--small btn--outline header-cta">Prenota un Tavolo</a>
            <button class="icon-btn cart-toggle" type="button" data-cart-open aria-label="Apri il carrello">
                <?= icon('bag') ?><span class="cart-count" data-cart-count hidden>0</span>
            </button>
            <button class="icon-btn nav-toggle" type="button" data-nav-open aria-label="Apri il menu di navigazione" aria-expanded="false" aria-controls="nav-dialog"><?= icon('menu') ?></button>
        </div>
    </div>
</header>

<main id="main">
    <!-- HERO -->
    <section class="hero" id="home" aria-labelledby="hero-title">
        <div class="hero-media" data-parallax="0.12">
            <img src="assets/images/hero.webp" alt="Sala di un’osteria storica: archi in pietra, tavoli in legno e candele accese" width="1920" height="1080" fetchpriority="high" decoding="async">
        </div>
        <div class="hero-shade" aria-hidden="true"></div>
        <div class="hero-light" aria-hidden="true"></div>
        <div class="dust" id="hero-dust" aria-hidden="true"></div>
        <div class="container hero-content">
            <p class="eyebrow eyebrow--light hero-eyebrow"><span class="eyebrow-line"></span>Parma, Italia · dal <?= e($restaurant['since']) ?><span class="eyebrow-line"></span></p>
            <h1 id="hero-title" class="hero-title" data-split>La Tradizione Italiana<br><em>dal Cuore di Parma</em></h1>
            <p class="hero-subtitle">Pasta tirata a mano, ricette di famiglia e il calore di una tavola apparecchiata. Qui ogni serata ha il sapore di casa.</p>
            <div class="hero-actions">
                <a href="#prenotazione" class="btn btn--gold">Prenota Ora <?= icon('arrow-right') ?></a>
                <a href="#menu" class="btn btn--ghost">Scopri il Menù <?= icon('arrow-down') ?></a>
            </div>
        </div>
        <div class="hero-seal" aria-hidden="true">
            <svg viewBox="0 0 140 140"><defs><path id="seal-circle" d="M70,70 m-52,0 a52,52 0 1,1 104,0 a52,52 0 1,1 -104,0"/></defs><circle cx="70" cy="70" r="66"/><circle cx="70" cy="70" r="40"/><text><textPath href="#seal-circle" startOffset="0">FATTO A MANO · CON AMORE · OGNI GIORNO · </textPath></text><path d="M70 91V51m0 12c-8 0-12-5-12-10 7 0 12 4 12 10Zm0 12c-8 0-12-5-12-10 7 0 12 4 12 10Zm0-12c8 0 12-5 12-10-7 0-12 4-12 10Zm0 12c8 0 12-5 12-10-7 0-12 4-12 10Z"/></svg>
        </div>
        <div class="hero-bottom container">
            <div class="hero-info"><span><?= icon('pin') ?><?= e($restaurant['address']) ?> · Parma</span><span><?= icon('clock') ?>Mar–Dom · 12:00–15:00 / 19:00–23:00</span></div>
            <a href="#storia" class="scroll-cue" aria-label="Scorri alla nostra storia"><span>La nostra storia</span><?= icon('arrow-down') ?></a>
        </div>
    </section>

    <!-- STORIA -->
    <section class="section section--story" id="storia" aria-labelledby="storia-title">
        <div class="container story-grid">
            <div class="story-visual" data-reveal>
                <figure class="story-photo frame" data-parallax="0.06"><img src="assets/images/parma-street.webp" alt="Un borgo dalle facciate calde, ispirazione per la nostra osteria" width="900" height="1200" loading="lazy" decoding="async"><figcaption>Le strade che ci hanno insegnato l’accoglienza.</figcaption></figure>
                <figure class="story-inset"><img src="assets/images/pasta-making.webp" alt="Mani esperte stendono la sfoglia su un piano infarinato" width="600" height="800" loading="lazy" decoding="async"></figure>
                <div class="year-seal"><span>Dal</span><strong><?= e($restaurant['since']) ?></strong><span>con amore</span></div>
            </div>
            <div class="story-copy">
                <p class="eyebrow" data-reveal>01 · Le nostre radici</p>
                <h2 id="storia-title" class="section-title" data-split>Alcune storie si scrivono.<br><em>Le nostre si cucinano.</em></h2>
                <p class="lead" data-reveal>Nel cuore di Parma, dietro un portone di legno, il tempo ha imparato ad aspettare.</p>
                <p data-reveal>Nel 1962 nonna Adelaide aprì una piccola osteria con sei tavoli, una cantina e un’idea semplice: trattare ogni ospite come un parente che torna a casa. Oggi quei tavoli sono qualcuno in più, ma la sfoglia si tira ancora a mano e il ragù sobbolle lento, come allora.</p>
                <p data-reveal>La nostra è una cucina di territorio: Parmigiano Reggiano, Prosciutto di Parma, verdure dell’orto e vini di piccoli produttori. Non cerchiamo effetti speciali. Cerchiamo il sapore delle cose fatte bene.</p>
                <div class="stats" data-reveal>
                    <div class="stat"><strong data-count="<?= $year - $restaurant['since'] ?>"><?= $year - $restaurant['since'] ?></strong><span>Anni di esperienza</span></div>
                    <div class="stat"><strong data-count="24">24</strong><span>Ricette tradizionali</span></div>
                    <div class="stat"><strong data-count="100" data-suffix="%">100%</strong><span>Ingredienti locali</span></div>
                </div>
                <div class="signature-block" data-reveal><span class="signature">La famiglia Moretti</span><span class="signature-role">Custodi di tradizione, dal 1962</span></div>
            </div>
        </div>
    </section>

    <!-- MENU -->
    <section class="section section--menu paper" id="menu" aria-labelledby="menu-title">
        <div class="container">
            <div class="section-heading section-heading--center">
                <p class="eyebrow" data-reveal>02 · Dalla nostra cucina</p>
                <h2 id="menu-title" class="section-title" data-split>Il menù <em>della tradizione</em></h2>
                <p class="section-intro" data-reveal>Ricette che non hanno bisogno di essere reinventate. Solo di essere ricordate.</p>
            </div>
            <div class="menu-tabs" role="tablist" aria-label="Categorie del menù" data-reveal>
                <?php $first = true; foreach ($menu as $key => $category): ?>
                    <button class="menu-tab<?= $first ? ' is-active' : '' ?>" id="tab-<?= e($key) ?>" type="button" role="tab" aria-selected="<?= $first ? 'true' : 'false' ?>" aria-controls="panel-<?= e($key) ?>" tabindex="<?= $first ? '0' : '-1' ?>" data-menu-tab="<?= e($key) ?>"><?= icon($category['icon']) ?><span><?= e($category['label']) ?></span></button>
                <?php $first = false; endforeach; ?>
            </div>
            <div class="menu-panels">
                <?php $first = true; foreach ($menu as $key => $category): ?>
                    <div class="menu-panel" id="panel-<?= e($key) ?>" role="tabpanel" aria-labelledby="tab-<?= e($key) ?>" <?= $first ? '' : 'hidden' ?>>
                        <p class="menu-note"><?= e($category['note']) ?></p>
                        <div class="dish-grid"><?php foreach ($category['items'] as $dish) { render_dish($dish); } ?></div>
                    </div>
                <?php $first = false; endforeach; ?>
            </div>
            <div class="menu-footer" data-reveal>
                <p><?= icon('wheat') ?>Coperto € 2,50 · Pane e grissini artigianali inclusi. Per allergie e intolleranze, parlate con noi.</p>
                <a href="#consegna" class="text-link">Porta la tradizione a casa <?= icon('arrow-right') ?></a>
            </div>
        </div>
    </section>

    <!-- CANTINA -->
    <section class="section section--cantina" id="cantina" aria-labelledby="cantina-title">
        <div class="container cantina-grid">
            <div class="cantina-visual" data-reveal>
                <img src="assets/images/cantina-barrels.webp" alt="Botti di rovere in una cantina in pietra, illuminate da luce calda" width="1200" height="900" loading="lazy" decoding="async" data-parallax="0.08">
                <div class="cantina-quote"><span class="quote-mark">“</span><p>Il vino è la poesia<br>della terra.</p><span>— detto dei nostri nonni</span></div>
                <div class="cantina-seal"><strong>80</strong><span>etichette<br>selezionate</span></div>
            </div>
            <div class="cantina-copy">
                <p class="eyebrow eyebrow--light" data-reveal>03 · La cantina</p>
                <h2 id="cantina-title" class="section-title" data-split>Ogni bottiglia<br><em>ha una storia.</em></h2>
                <p class="lead" data-reveal>Sotto la sala, tra pietra e legno, custodiamo il carattere di questo territorio.</p>
                <p data-reveal>Lambrusco che sorride, Malvasia che profuma di fiori, rossi importanti che chiedono tempo. Scegliamo piccoli vignaioli e bottiglie sincere, per accompagnare — mai coprire — il sapore della nostra cucina.</p>
                <ul class="wine-list" data-reveal>
                    <li><div><strong>Lambrusco del Borgo</strong><span>Emilia-Romagna DOC · Rosso frizzante</span></div><span class="wine-year">2023</span><span class="wine-price">€ 24</span></li>
                    <li><div><strong>Malvasia delle Colline</strong><span>Colli Piacentini DOC · Bianco fermo</span></div><span class="wine-year">2022</span><span class="wine-price">€ 26</span></li>
                    <li><div><strong>Rosso della Riserva</strong><span>Gutturnio DOC · Affinato in rovere</span></div><span class="wine-year">2019</span><span class="wine-price">€ 32</span></li>
                </ul>
                <button class="btn btn--outline" type="button" data-menu-target="vini" data-reveal>Esplora la carta dei vini <?= icon('arrow-right') ?></button>
            </div>
        </div>
    </section>

    <!-- GALLERIA -->
    <section class="section section--gallery" id="galleria" aria-labelledby="galleria-title">
        <div class="container section-heading section-heading--split">
            <div><p class="eyebrow eyebrow--light" data-reveal>04 · Sguardi di casa</p><h2 id="galleria-title" class="section-title" data-split>La bellezza <em>dei gesti veri.</em></h2></div>
            <div class="gallery-controls" data-reveal><button class="icon-btn gallery-prev" type="button" aria-label="Foto precedente"><?= icon('arrow-left') ?></button><span class="gallery-fraction" aria-live="polite">01 / 06</span><button class="icon-btn gallery-next" type="button" aria-label="Foto successiva"><?= icon('arrow-right') ?></button></div>
        </div>
        <div class="gallery-swiper swiper" data-reveal>
            <div class="swiper-wrapper">
                <?php foreach ($gallery as $index => $photo): ?>
                    <div class="swiper-slide">
                        <button class="gallery-item" type="button" data-lightbox="<?= $index ?>" aria-label="Ingrandisci la foto: <?= e($photo['title']) ?>">
                            <img src="assets/images/<?= e($photo['image']) ?>.webp" alt="<?= e($photo['title']) ?>" width="1200" height="900" loading="lazy" decoding="async">
                            <span class="gallery-caption"><strong><?= e($photo['title']) ?></strong><span><?= e($photo['caption']) ?></span></span>
                            <span class="gallery-zoom"><?= icon('zoom') ?></span>
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <p class="container gallery-hint">Trascinate, esplorate, restate un momento.</p>
    </section>

    <!-- CHEF -->
    <section class="section section--chef" id="chef" aria-labelledby="chef-title">
        <div class="container chef-grid">
            <div class="chef-copy">
                <p class="eyebrow eyebrow--light" data-reveal>05 · Le mani, il cuore</p>
                <h2 id="chef-title" class="section-title" data-split>La cucina non è<br><em>una ricetta.</em></h2>
                <blockquote data-reveal>“È il modo in cui ti faccio sentire a casa. Mio nonno mi diceva: cucina per qualcuno, non per qualcosa. Io lo faccio ancora.”</blockquote>
                <p data-reveal>Alessandro Moretti, terza generazione, guida la cucina con la stessa ostinazione gentile del nonno. Ogni mattina controlla la sfoglia, assaggia il ragù, saluta i fornitori per nome. Il resto viene da sé.</p>
                <div class="signature-block" data-reveal><span class="signature">Alessandro Moretti</span><span class="signature-role">Chef e custode della tradizione</span></div>
            </div>
            <figure class="chef-portrait frame" data-reveal data-parallax="0.05"><img src="assets/images/chef.webp" alt="Alessandro Moretti prepara pasta fresca nella cucina dell’osteria" width="1000" height="1240" loading="lazy" decoding="async"><figcaption>Alessandro, tra farina e memoria.</figcaption></figure>
        </div>
    </section>

    <!-- PRENOTAZIONE -->
    <section class="section section--booking" id="prenotazione" aria-labelledby="booking-title">
        <div class="container booking-grid">
            <aside class="booking-aside">
                <p class="eyebrow eyebrow--light" data-reveal>06 · Il vostro tavolo</p>
                <h2 id="booking-title" class="section-title" data-split>Il vostro posto<br><em>è qui.</em></h2>
                <p class="lead" data-reveal>Che sia una cena a due, un pranzo di famiglia o un brindisi tra amici, apparecchiamo per voi.</p>
                <figure class="booking-photo" data-reveal><img src="assets/images/candle-table.webp" alt="Tavolo apparecchiato con candele e calici di vino" width="900" height="1200" loading="lazy" decoding="async"></figure>
                <div class="hours-card" data-reveal>
                    <div class="candle" aria-hidden="true"><span class="candle-glow"></span><svg class="candle-flame" viewBox="0 0 20 30"><path d="M10 2c4 7 7 10 7 16a7 7 0 0 1-14 0c0-6 3-9 7-16Z"/></svg><span class="candle-body"></span></div>
                    <div><strong>Quando le candele si accendono</strong><p>Martedì – Domenica<br>12:00–15:00 · 19:00–23:00<br><span>Lunedì riposo</span></p></div>
                </div>
                <p class="booking-phone" data-reveal>Per gruppi oltre 8 persone: <a href="tel:<?= e($restaurant['phone_href']) ?>"><?= e($restaurant['phone']) ?></a></p>
            </aside>
            <div class="booking-form-wrap paper" data-reveal>
                <div class="form-heading"><h3>Prenota un tavolo</h3><p>Vi ricontattiamo personalmente per confermare.</p></div>
                <form id="booking-form" class="form" novalidate>
                    <div class="form-row">
                        <div class="field"><label for="bk-name">Nome e cognome *</label><input id="bk-name" name="name" type="text" autocomplete="name" required minlength="2" maxlength="100" placeholder="Il vostro nome"><span class="field-error" aria-live="polite"></span></div>
                        <div class="field"><label for="bk-phone">Telefono *</label><input id="bk-phone" name="phone" type="tel" autocomplete="tel" required maxlength="25" placeholder="+39 ..."><span class="field-error" aria-live="polite"></span></div>
                    </div>
                    <div class="field"><label for="bk-email">Email *</label><input id="bk-email" name="email" type="email" autocomplete="email" required maxlength="254" placeholder="nome@esempio.it"><span class="field-error" aria-live="polite"></span></div>
                    <div class="form-row form-row--three">
                        <div class="field"><label for="bk-guests">Numero di persone *</label><select id="bk-guests" name="guests" required><?php for ($i = 1; $i <= 8; $i++): ?><option value="<?= $i ?>"><?= $i ?> <?= $i === 1 ? 'persona' : 'persone' ?></option><?php endfor; ?></select><span class="field-error" aria-live="polite"></span></div>
                        <div class="field"><label for="bk-date">Data *</label><input id="bk-date" name="date" type="date" required min="<?= e($today->format('Y-m-d')) ?>" max="<?= e($maxDate->format('Y-m-d')) ?>"><span class="field-error" aria-live="polite"></span></div>
                        <div class="field"><label for="bk-time">Ora *</label><select id="bk-time" name="time" required><option value="">Scegliete</option><optgroup label="Pranzo"><option>12:00</option><option>12:30</option><option>13:00</option><option>13:30</option><option>14:00</option></optgroup><optgroup label="Cena"><option>19:00</option><option>19:30</option><option>20:00</option><option>20:30</option><option>21:00</option><option>21:30</option></optgroup></select><span class="field-error" aria-live="polite"></span></div>
                    </div>
                    <fieldset class="field room-field"><legend>Dove preferite stare?</legend><div class="room-options"><label class="room-option"><input type="radio" name="room" value="Sala" checked><span><?= icon('flame') ?>Sala storica<small>Legno, pietra e calore</small></span></label><label class="room-option"><input type="radio" name="room" value="Terrazza"><span><?= icon('leaf') ?>Terrazza<small>Sotto il cielo di Parma</small></span></label></div></fieldset>
                    <div class="field"><label for="bk-notes">Richieste particolari <span class="optional">(facoltativo)</span></label><textarea id="bk-notes" name="notes" rows="3" maxlength="1000" placeholder="Allergie, ricorrenze, seggiolone..."></textarea></div>
                    <label class="consent"><input type="checkbox" name="consent" required><span>Acconsento al trattamento dei dati per gestire la richiesta. *</span></label><span class="field-error" data-error-for="consent" aria-live="polite"></span>
                    <button class="btn btn--wine btn--full" type="submit" disabled>Prenota <?= icon('arrow-right') ?></button>
                    <p class="form-note"><?= icon('spark') ?>Prototipo dimostrativo: nessuna prenotazione reale verrà effettuata.</p>
                </form>
            </div>
        </div>
    </section>

    <!-- ORDINA -->
    <section class="section section--order paper" id="consegna" aria-labelledby="order-title">
        <div class="container">
            <div class="section-heading section-heading--split">
                <div><p class="eyebrow" data-reveal>07 · A casa vostra</p><h2 id="order-title" class="section-title" data-split>La tradizione <em>viaggia con voi.</em></h2></div>
                <button class="btn btn--outline btn--dark" type="button" data-cart-open data-reveal><?= icon('bag') ?>Il vostro ordine <span data-cart-count-inline>(0)</span></button>
            </div>
            <div class="delivery-modes" role="radiogroup" aria-label="Modalità di ordine" data-reveal>
                <label class="mode-card"><input type="radio" name="order-mode" value="delivery" checked><span class="mode-content"><span class="mode-icon"><?= icon('truck') ?></span><span class="mode-text"><strong>Consegna</strong><span>A Parma e dintorni, in 35–50 minuti.</span><small>€ 4,00 · gratuita sopra € 50,00</small></span><span class="mode-check"><?= icon('check') ?></span></span></label>
                <label class="mode-card"><input type="radio" name="order-mode" value="pickup"><span class="mode-content"><span class="mode-icon"><?= icon('takeaway') ?></span><span class="mode-text"><strong>Asporto</strong><span>Ritirate in osteria, quando preferite.</span><small>Pronto in 20–30 minuti · nessun costo</small></span><span class="mode-check"><?= icon('check') ?></span></span></label>
            </div>
            <div class="quick-order-heading" data-reveal><h3>I preferiti da portare a casa</h3><button class="text-link text-link--dark" type="button" data-menu-target="paste">Tutto il menù <?= icon('arrow-right') ?></button></div>
            <div class="dish-grid dish-grid--quick">
                <?php foreach (['antipasto-della-casa', 'tortelli-erbetta', 'tagliata-di-manzo', 'tiramisu-della-casa'] as $id) { foreach ($menu as $category) { foreach ($category['items'] as $dish) { if ($dish['id'] === $id) { render_dish($dish, true); } } } } ?>
            </div>
            <p class="order-note" data-reveal><?= icon('clock') ?>Cucina espressa al momento dell’ordine. I piatti viaggiano in contenitori adatti a mantenerne la temperatura.</p>
        </div>
    </section>

    <!-- RECENSIONI -->
    <section class="section section--reviews" id="recensioni" aria-labelledby="reviews-title">
        <div class="container section-heading section-heading--center">
            <p class="eyebrow eyebrow--light" data-reveal>08 · Le voci dei nostri ospiti</p>
            <h2 id="reviews-title" class="section-title" data-split>Il grazie più bello <em>è il vostro.</em></h2>
        </div>
        <div class="container reviews-swiper swiper" data-reveal>
            <div class="swiper-wrapper">
                <?php foreach ($reviews as $review): ?>
                    <div class="swiper-slide"><article class="review-card"><div class="stars" role="img" aria-label="5 stelle su 5"><?php for ($i = 0; $i < 5; $i++) { echo icon('star'); } ?></div><blockquote>“<?= e($review['text']) ?>”</blockquote><div class="review-author"><img src="assets/images/<?= e($review['avatar']) ?>.webp" alt="" width="56" height="56" loading="lazy" decoding="async"><div><strong><?= e($review['name']) ?></strong><span><?= e($review['detail']) ?></span></div></div></article></div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="container reviews-controls" data-reveal><button class="icon-btn reviews-prev" type="button" aria-label="Recensione precedente"><?= icon('arrow-left') ?></button><div class="reviews-pagination"></div><button class="icon-btn reviews-next" type="button" aria-label="Recensione successiva"><?= icon('arrow-right') ?></button></div>
        <p class="demo-caption">Recensioni illustrative, scritte per raccontare un’esperienza possibile.</p>
    </section>

    <!-- FAQ -->
    <section class="section section--faq" id="faq" aria-labelledby="faq-title">
        <div class="container faq-grid">
            <div class="faq-intro"><p class="eyebrow eyebrow--light" data-reveal>09 · Prima di venire</p><h2 id="faq-title" class="section-title" data-split>Qualcosa<br><em>da chiederci?</em></h2><p data-reveal>Le risposte alle domande più comuni. Per tutto il resto, siamo al telefono.</p><a class="text-link" href="tel:<?= e($restaurant['phone_href']) ?>" data-reveal><?= icon('phone') ?><?= e($restaurant['phone']) ?></a></div>
            <div class="faq-list" data-reveal>
                <?php foreach ($faqs as $index => $faq): ?>
                    <div class="faq-item"><h3><button type="button" class="faq-question" aria-expanded="<?= $index === 0 ? 'true' : 'false' ?>" aria-controls="faq-panel-<?= $index ?>" id="faq-button-<?= $index ?>"><span><?= e($faq['q']) ?></span><?= icon('plus') ?></button></h3><div class="faq-panel" id="faq-panel-<?= $index ?>" role="region" aria-labelledby="faq-button-<?= $index ?>" <?= $index === 0 ? '' : 'hidden' ?>><p><?= e($faq['a']) ?></p></div></div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- CONTATTI -->
    <section class="section section--contact" id="contatti" aria-labelledby="contact-title">
        <div class="container contact-grid">
            <div class="contact-copy">
                <p class="eyebrow eyebrow--light" data-reveal>10 · Venite a trovarci</p>
                <h2 id="contact-title" class="section-title" data-split>Nel cuore di Parma,<br><em>fuori dal tempo.</em></h2>
                <ul class="contact-list" data-reveal>
                    <li><?= icon('pin') ?><div><strong>Indirizzo</strong><span><?= e($restaurant['address']) ?><br><?= e($restaurant['city']) ?></span></div></li>
                    <li><?= icon('phone') ?><div><strong>Telefono</strong><a href="tel:<?= e($restaurant['phone_href']) ?>"><?= e($restaurant['phone']) ?></a></div></li>
                    <li><?= icon('mail') ?><div><strong>Email</strong><a href="mailto:<?= e($restaurant['email']) ?>"><?= e($restaurant['email']) ?></a></div></li>
                    <li><?= icon('clock') ?><div><strong>Orari</strong><span>Mar–Dom 12:00–15:00 / 19:00–23:00<br>Lunedì chiuso</span></div></li>
                </ul>
                <a class="btn btn--outline" href="https://www.google.com/maps/search/?api=1&query=centro+storico+Parma+Italia" target="_blank" rel="noopener noreferrer" data-reveal>Indicazioni stradali <?= icon('arrow-right') ?></a>
            </div>
            <div class="map-wrap" data-reveal>
                <svg class="map" viewBox="0 0 600 460" role="img" aria-label="Mappa illustrativa del centro storico di Parma, non in scala">
                    <rect width="600" height="460" fill="#221A16"/>
                    <g class="map-blocks" fill="#2c211b" stroke="#3B2A22"><path d="M40 40h120v80H40zM190 30h100v90H190zM320 40h110v70H320zM460 30h110v90H460zM30 160h130v90H30zM190 150h100v100H190zM440 150h130v100H440zM40 290h120v110H40zM190 290h100v120H190zM330 290h100v110H330zM460 290h110v120H460z"/></g>
                    <g fill="none" stroke="#A77946" stroke-width="2" opacity=".35"><path d="M0 140h600M0 270h600M175 0v460M310 0v180M310 270v190M445 0v460"/><path d="M0 340C150 300 250 360 360 330s160-40 240-20" stroke="#55624B" stroke-width="14" opacity=".5"/></g>
                    <g fill="#C8B9A4" font-family="Manrope, sans-serif" font-size="11" letter-spacing="2" opacity=".7"><text x="330" y="190">DUOMO</text><text x="195" y="260">PIAZZA GARIBALDI</text><text x="50" y="430">TORRENTE PARMA</text><text x="460" y="25">PARMA</text></g>
                    <circle cx="370" cy="225" r="34" fill="#C8A66A" opacity=".08"/><circle cx="370" cy="225" r="20" fill="#C8A66A" opacity=".12"/>
                    <g transform="translate(354 196)"><path d="M16 44S32 29 32 17A16 16 0 1 0 0 17c0 12 16 27 16 27Z" fill="#C8A66A"/><circle cx="16" cy="17" r="6" fill="#171310"/></g>
                    <text x="370" y="290" text-anchor="middle" fill="#F5EFE6" font-family="Cinzel, serif" font-size="12" letter-spacing="2">OSTERIA DEL BORGO</text>
                </svg>
                <div class="map-card"><span class="brand-mark" aria-hidden="true"><?= icon('wheat') ?></span><div><strong>Osteria del Borgo</strong><span>Centro storico · Parma</span></div></div>
                <span class="map-note">Mappa illustrativa</span>
            </div>
        </div>
    </section>
</main>

<footer class="site-footer">
    <div class="container footer-top">
        <div class="footer-brand"><span class="footer-wordmark">Osteria <em>del Borgo</em></span><p>Una tavola. Una famiglia. Una storia.<br>Parma, dal <?= e($restaurant['since']) ?>.</p><div class="socials"><a href="https://www.instagram.com/" target="_blank" rel="noopener noreferrer" aria-label="Instagram"><?= icon('instagram') ?></a><a href="https://www.facebook.com/" target="_blank" rel="noopener noreferrer" aria-label="Facebook"><?= icon('facebook') ?></a></div></div>
        <div class="footer-col"><h3>Esplora</h3><a href="#storia">La Nostra Storia</a><a href="#menu">Menù</a><a href="#cantina">La Cantina</a><a href="#galleria">Galleria</a><a href="#chef">Lo Chef</a></div>
        <div class="footer-col"><h3>Ospiti</h3><a href="#prenotazione">Prenotazione</a><a href="#consegna">Consegna e Asporto</a><a href="#recensioni">Recensioni</a><a href="#faq">Domande frequenti</a><a href="#contatti">Contatti</a></div>
        <div class="footer-newsletter"><h3>Restiamo in contatto</h3><p>Stagioni, serate e piccole notizie dal borgo. Una lettera ogni tanto, mai invadente.</p><form id="newsletter-form" class="newsletter-form" novalidate><label class="sr-only" for="newsletter-email">La vostra email</label><input id="newsletter-email" name="email" type="email" required placeholder="La vostra email" autocomplete="email"><button type="submit" disabled aria-label="Iscriviti alla newsletter"><?= icon('arrow-right') ?></button></form><p class="newsletter-message" id="newsletter-message" role="status" hidden></p><span class="newsletter-note">Niente spam. Solo il profumo di casa.</span></div>
    </div>
    <div class="container footer-bottom"><span>© <?= e($year) ?> <?= e($restaurant['name']) ?> · Tutti i diritti riservati</span><div><button type="button" data-legal="privacy">Privacy</button><button type="button" data-legal="note">Note legali</button><button type="button" data-legal="cookie">Cookie</button></div><span class="footer-credit">Heritage Collection · Prototipo dimostrativo</span></div>
    <p class="container prototype-note">Nome, storia, indirizzi, contatti, prezzi e recensioni sono fittizi. Le immagini hanno scopo illustrativo. Nessun dato viene inviato a un server.</p>
</footer>

<button class="back-to-top icon-btn" type="button" aria-label="Torna all’inizio"><?= icon('arrow-down') ?></button>
<div class="toast-region" id="toast-region" aria-live="polite" aria-atomic="true"></div>

<!-- NAVIGAZIONE MOBILE -->
<dialog class="nav-dialog" id="nav-dialog" aria-label="Navigazione">
    <div class="nav-dialog-head"><span class="brand-name">del Borgo</span><button class="icon-btn" type="button" data-nav-close aria-label="Chiudi il menu"><?= icon('close') ?></button></div>
    <nav class="mobile-links" aria-label="Navigazione mobile"><a href="#home">Home</a><a href="#storia">La Nostra Storia</a><a href="#menu">Menù</a><a href="#galleria">Galleria</a><a href="#prenotazione">Prenotazione</a><a href="#consegna">Consegna</a><a href="#contatti">Contatti</a></nav>
    <a href="#prenotazione" class="btn btn--gold btn--full">Prenota un Tavolo <?= icon('arrow-right') ?></a>
    <p class="nav-dialog-foot">Parma · dal <?= e($restaurant['since']) ?></p>
</dialog>

<!-- CARRELLO -->
<dialog class="cart-drawer" id="cart-drawer" aria-labelledby="cart-title">
    <div class="drawer-head"><div><p class="eyebrow">A casa vostra</p><h2 id="cart-title">Il vostro ordine</h2></div><button class="icon-btn" type="button" data-cart-close aria-label="Chiudi il carrello"><?= icon('close') ?></button></div>
    <div class="drawer-mode" id="drawer-mode"><?= icon('truck') ?><span>Consegna a Parma e dintorni</span></div>
    <div class="cart-scroll">
        <div class="cart-empty" id="cart-empty"><?= icon('bag') ?><h3>Il carrello è ancora vuoto</h3><p>Lasciatevi guidare dal profumo della nostra cucina.</p><button class="btn btn--outline btn--dark" type="button" data-menu-target="paste">Scopri il menù <?= icon('arrow-right') ?></button></div>
        <ul class="cart-items" id="cart-items" aria-label="Piatti nel carrello"></ul>
        <div class="cart-summary" id="cart-summary" hidden><div><span>Subtotale</span><span data-summary="subtotal">€ 0,00</span></div><div><span data-summary="shipping-label">Consegna</span><span data-summary="shipping">€ 4,00</span></div><div class="cart-total"><span>Totale</span><span data-summary="total">€ 0,00</span></div><p class="cart-shipping-note" data-summary="shipping-note"></p></div>
        <form id="checkout-form" class="form checkout-form" novalidate hidden>
            <button class="text-link text-link--dark checkout-back" type="button"><?= icon('arrow-left') ?>Torna al carrello</button>
            <h3>Dettagli <?= icon('arrow-down') ?></h3>
            <div class="field"><label for="co-name">Nome e cognome *</label><input id="co-name" name="name" type="text" autocomplete="name" required minlength="2" maxlength="100"><span class="field-error" aria-live="polite"></span></div>
            <div class="field"><label for="co-phone">Telefono *</label><input id="co-phone" name="phone" type="tel" autocomplete="tel" required maxlength="25"><span class="field-error" aria-live="polite"></span></div>
            <div class="field"><label for="co-email">Email *</label><input id="co-email" name="email" type="email" autocomplete="email" required maxlength="254"><span class="field-error" aria-live="polite"></span></div>
            <div class="field" data-delivery-field><label for="co-address">Indirizzo di consegna *</label><input id="co-address" name="address" type="text" autocomplete="street-address" placeholder="Via, numero civico, citofono"><span class="field-error" aria-live="polite"></span></div>
            <div class="field"><label for="co-time">Orario preferito</label><select id="co-time" name="time"><option value="asap">Prima disponibilità</option><option value="12:30">12:30</option><option value="13:00">13:00</option><option value="19:30">19:30</option><option value="20:00">20:00</option><option value="20:30">20:30</option><option value="21:00">21:00</option></select></div>
            <div class="field"><label for="co-notes">Note <span class="optional">(facoltativo)</span></label><textarea id="co-notes" name="notes" rows="2" maxlength="1000" placeholder="Allergie, citofono, istruzioni..."></textarea></div>
            <fieldset class="field payment-field"><legend>Modalità di pagamento</legend><label class="payment-option"><input type="radio" name="payment" value="later" checked><span id="payment-label">Alla consegna</span></label><p class="form-note">Nessun pagamento online. Ordine simulato.</p></fieldset>
            <label class="consent"><input type="checkbox" name="consent" required><span>Acconsento al trattamento dei dati per gestire l’ordine. *</span></label><span class="field-error" data-error-for="consent" aria-live="polite"></span>
            <button class="btn btn--wine btn--full" type="submit" disabled>Conferma ordine <?= icon('check') ?></button>
        </form>
    </div>
    <div class="drawer-foot" id="drawer-foot"><button class="btn btn--wine btn--full" type="button" id="checkout-button" disabled>Procedi all’ordine <?= icon('arrow-right') ?></button><p class="form-note">Ordine dimostrativo · nessun addebito</p></div>
</dialog>

<!-- LIGHTBOX -->
<dialog class="lightbox" id="lightbox" aria-label="Galleria fotografica ingrandita">
    <button class="icon-btn lightbox-close" type="button" data-lightbox-close aria-label="Chiudi la foto"><?= icon('close') ?></button>
    <button class="icon-btn lightbox-prev" type="button" aria-label="Foto precedente"><?= icon('arrow-left') ?></button>
    <figure class="lightbox-figure"><div class="lightbox-image-wrap"><img id="lightbox-image" src="assets/images/hero.webp" alt=""></div><figcaption><strong id="lightbox-title"></strong><span id="lightbox-caption"></span></figcaption></figure>
    <button class="icon-btn lightbox-next" type="button" aria-label="Foto successiva"><?= icon('arrow-right') ?></button>
    <button class="icon-btn lightbox-zoom" type="button" aria-label="Attiva o disattiva lo zoom" aria-pressed="false"><?= icon('zoom') ?></button>
    <span class="lightbox-counter" aria-live="polite"></span>
</dialog>

<!-- CONFERMA -->
<dialog class="modal success-modal" id="success-dialog" aria-labelledby="success-title">
    <div class="confetti" id="confetti" aria-hidden="true"></div>
    <button class="icon-btn modal-close" type="button" data-success-close aria-label="Chiudi la conferma"><?= icon('close') ?></button>
    <span class="success-icon"><?= icon('check') ?></span>
    <p class="eyebrow" id="success-eyebrow">Grazie</p>
    <h2 id="success-title">La tua richiesta è stata inviata.</h2>
    <p id="success-message"></p>
    <div class="success-summary" id="success-summary"></div>
    <button class="btn btn--wine" type="button" data-success-close>Perfetto, grazie <?= icon('check') ?></button>
    <p class="form-note">Simulazione completata. Nessuna prenotazione o ordine reale è stato registrato.</p>
</dialog>

<!-- NOTE LEGALI -->
<dialog class="modal legal-modal" id="legal-dialog" aria-labelledby="legal-title">
    <button class="icon-btn modal-close" type="button" data-legal-close aria-label="Chiudi le note"><?= icon('close') ?></button>
    <p class="eyebrow">Osteria del Borgo</p><h2 id="legal-title"></h2><div class="legal-body" id="legal-body"></div>
    <button class="btn btn--outline btn--dark" type="button" data-legal-close>Ho capito</button>
</dialog>

<noscript><div class="noscript-note">Per prenotazioni, ordini e galleria interattiva attivate JavaScript. Il menù completo resta leggibile qui sotto.</div></noscript>

<script type="application/json" id="catalog-data"><?= json_encode($catalog, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?></script>
<script type="application/json" id="gallery-data"><?= json_encode($gallery, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?></script>
<script src="assets/vendor/gsap.min.js" defer></script>
<script src="assets/vendor/ScrollTrigger.min.js" defer></script>
<script src="assets/vendor/lenis.min.js" defer></script>
<script src="assets/vendor/swiper-bundle.min.js" defer></script>
<script src="assets/js/app.js" defer></script>
</body>
</html>
