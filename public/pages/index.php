<?php
require_once __DIR__ . '/../../src/auth.php';
require_once __DIR__ . '/../../src/motorcycles.php';
$config   = require __DIR__ . '/../../src/config.php';
$user     = isLogged() ? getCurrentUser() : null;
$gmapsKey = htmlspecialchars($config['google_maps_api_key'] ?? '', ENT_QUOTES, 'UTF-8');
$stripePk = htmlspecialchars($config['stripe']['public_key'] ?? '', ENT_QUOTES, 'UTF-8');
$pageTitle = 'MotoTransport - Home';
$extraCss  = ['css/modules/home.css', 'css/modules/footer.css', 'css/modules/quote-modal.css'];

// Dati utente per autocompletamento modale (solo campi necessari, mai password)
$quoteUserData = null;
$quoteUserMotos = [];
if ($user) {
    $quoteUserData = [
        'nome'     => trim(($user['nome'] ?? '') . ' ' . ($user['cognome'] ?? '')),
        'email'    => $user['email'] ?? '',
        'telefono' => $user['telefono'] ?? '',
        'cf'       => $user['codice_fiscale_azienda'] ?? '',
    ];
    $quoteUserMotos = getUserMotorcycles((int)$user['id']);
}
?>
<!DOCTYPE html>
<html lang="it">

<head>
    <?php include __DIR__ . '/../includes/head.php'; ?>
</head>

<body>
    <?php include __DIR__ . '/../includes/navbar.php'; ?>

    <!-- Hero Section -->
    <section class="hero" id="home">
        <div class="hero__container">
            <div class="hero__content">
                <h1 class="hero__title">
                    Trasportiamo la tua<br>
                    moto in <span class="hero__title--highlight">tutta<br>
                        sicurezza</span>
                </h1>
                <p class="hero__subtitle">
                    Servizio professionale di trasporto moto porta a porta in tutta Italia.<br>
                    Ritiro e consegna rapida, assicurazione completa e tracking in tempo reale.
                </p>
                <div class="hero__buttons">
                    <a href="#" class="btn btn--primary open-quote-modal">Preventivo Gratuito &rarr;</a>
                    <a href="#come-funziona" class="btn btn--white">Come funziona &darr;</a>
                </div>

                <!-- Hero Features -->
                <div class="hero__features">
                    <div class="hero__feature">
                        <div class="hero__feature-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                            </svg>
                        </div>
                        <div class="hero__feature-text">
                            <strong>100%</strong>
                            <span>Assicurazione</span>
                        </div>
                    </div>
                    <div class="hero__feature">
                        <div class="hero__feature-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12 6 12 12 16 14"></polyline>
                            </svg>
                        </div>
                        <div class="hero__feature-text">
                            <strong>24h</strong>
                            <span>Consegna</span>
                        </div>
                    </div>
                    <div class="hero__feature">
                        <div class="hero__feature-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                <circle cx="12" cy="10" r="3"></circle>
                            </svg>
                        </div>
                        <div class="hero__feature-text">
                            <strong>Italia</strong>
                            <span>Copertura</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Come Funziona Section -->
    <section class="how-it-works" id="come-funziona">
        <div class="how-it-works__container">
            <div class="how-it-works__badge">Processo Semplice</div>
            <h2 class="how-it-works__title">Come Funziona</h2>
            <p class="how-it-works__subtitle">Trasportare la tua moto non è mai stato così facile.<br>Tre semplici passi per un servizio impeccabile.</p>

            <div class="how-it-works__grid">
                <div class="how-it-works__card">
                    <div class="how-it-works__number">01</div>
                    <div class="how-it-works__card-icon">
                        <i class="fa-solid fa-file-contract"></i>
                    </div>
                    <h3 class="how-it-works__card-title">Richiedi Preventivo</h3>
                    <p class="how-it-works__card-description">
                        Compila il modulo con i dettagli del trasporto: tipo di moto, città di ritiro e consegna. Ricevi un preventivo immediato e personalizzato.
                    </p>
                </div>

                <div class="how-it-works__card">
                    <div class="how-it-works__number">02</div>
                    <div class="how-it-works__card-icon">
                        <i class="fa-solid fa-truck"></i>
                    </div>
                    <h3 class="how-it-works__card-title">Ritiro a Domicilio</h3>
                    <p class="how-it-works__card-description">
                        Il nostro team ritira la moto direttamente a casa tua o dove preferisci. La carichiamo con cura sul nostro mezzo specializzato.
                    </p>
                </div>

                <div class="how-it-works__card">
                    <div class="how-it-works__number">03</div>
                    <div class="how-it-works__card-icon">
                        <i class="fa-solid fa-stopwatch"></i>
                    </div>
                    <h3 class="how-it-works__card-title">Consegna Garantita</h3>
                    <p class="how-it-works__card-description">
                        Monitora il trasporto in tempo reale. Consegniamo la tua moto in perfette condizioni, con documentazione completa.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Perché Sceglierci Section -->
    <section class="why-choose-us" id="vantaggi">
        <div class="why-choose-us__container">
            <div class="why-choose-us__badge">I Nostri Punti di Forza</div>
            <h2 class="why-choose-us__title">Perché Sceglierci</h2>
            <p class="why-choose-us__subtitle">
                Affidabilità, professionalità e cura del dettaglio.<br>
                Ecco cosa ci distingue dalla concorrenza.
            </p>

            <div class="why-choose-us__grid">
                <div class="why-choose-us__card">
                    <div class="why-choose-us__card-icon"><i class="fa-solid fa-shield-halved"></i></div>
                    <h3 class="why-choose-us__card-title">Assicurazione Totale</h3>
                    <p class="why-choose-us__card-description">Copertura assicurativa completa per ogni trasporto. La tua moto è protetta dal ritiro alla consegna.</p>
                </div>
                <div class="why-choose-us__card">
                    <div class="why-choose-us__card-icon"><i class="fa-regular fa-clock"></i></div>
                    <h3 class="why-choose-us__card-title">Consegna Rapida</h3>
                    <p class="why-choose-us__card-description">Tempi di consegna da 24 a 72 ore in tutta Italia. Rispettiamo sempre le tempistiche concordate.</p>
                </div>
                <div class="why-choose-us__card">
                    <div class="why-choose-us__card-icon"><i class="fa-solid fa-location-dot"></i></div>
                    <h3 class="why-choose-us__card-title">Tracking in Tempo Reale</h3>
                    <p class="why-choose-us__card-description">Monitora la posizione della tua moto durante tutto il trasporto tramite la nostra piattaforma.</p>
                </div>
                <div class="why-choose-us__card">
                    <div class="why-choose-us__card-icon"><i class="fa-solid fa-headset"></i></div>
                    <h3 class="why-choose-us__card-title">Supporto 24/7</h3>
                    <p class="why-choose-us__card-description">Team dedicato disponibile 24 ore su 24 per qualsiasi esigenza o informazione sul trasporto.</p>
                </div>
                <div class="why-choose-us__card">
                    <div class="why-choose-us__card-icon"><i class="fa-solid fa-euro-sign"></i></div>
                    <h3 class="why-choose-us__card-title">Prezzi Trasparenti</h3>
                    <p class="why-choose-us__card-description">Preventivo chiaro e dettagliato senza costi nascosti. Paghi esattamente quanto concordato.</p>
                </div>
                <div class="why-choose-us__card">
                    <div class="why-choose-us__card-icon"><i class="fa-solid fa-medal"></i></div>
                    <h3 class="why-choose-us__card-title">Esperienza Decennale</h3>
                    <p class="why-choose-us__card-description">Oltre 10 anni di esperienza nel settore con migliaia di trasporti completati con successo.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Gallery Section -->
    <section class="gallery" id="gallery">
        <div class="gallery__container">
            <div class="gallery__badge">Il Nostro Lavoro</div>
            <h2 class="gallery__title">Gallery</h2>
            <p class="gallery__subtitle">Scopri come curiamo ogni dettaglio del trasporto della tua moto.</p>

            <div class="gallery__slider-wrapper">
                <button class="gallery__slider-btn gallery__slider-btn--prev" id="galleryPrev">
                    <i class="fa-solid fa-chevron-left"></i>
                </button>
                <div class="gallery__slider" id="gallerySlider">
                    <div class="gallery__item">
                        <div class="gallery__placeholder"></div>
                    </div>
                    <div class="gallery__item">
                        <div class="gallery__placeholder"></div>
                    </div>
                    <div class="gallery__item">
                        <div class="gallery__placeholder"></div>
                    </div>
                    <div class="gallery__item">
                        <div class="gallery__placeholder"></div>
                    </div>
                    <div class="gallery__item">
                        <div class="gallery__placeholder"></div>
                    </div>
                    <div class="gallery__item">
                        <div class="gallery__placeholder"></div>
                    </div>
                </div>
                <button class="gallery__slider-btn gallery__slider-btn--next" id="galleryNext">
                    <i class="fa-solid fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </section>

    <!-- Recensioni Section -->
    <section class="reviews" id="recensioni">
        <div class="reviews__container">
            <div class="reviews__header">
                <span class="reviews__badge">Cosa Dicono di Noi</span>
                <h2 class="reviews__title">Recensioni Clienti</h2>
                <p class="reviews__subtitle">La soddisfazione dei nostri clienti è la nostra priorità.<br>Ecco alcune delle loro esperienze.</p>
            </div>

            <div class="reviews__grid">
                <div class="reviews__card">
                    <div class="reviews__card-header">
                        <div class="reviews__stars">
                            <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
                        </div>
                        <i class="fa-solid fa-quote-right reviews__quote-icon"></i>
                    </div>
                    <p class="reviews__text">"Servizio impeccabile! La mia Ducati è arrivata in perfette condizioni. Comunicazione eccellente durante tutto il trasporto. Consigliato!"</p>
                    <div class="reviews__author">
                        <div class="reviews__author-info">
                            <strong class="reviews__author-name">Marco Rossi</strong>
                            <span class="reviews__author-route">Milano &rarr; Roma</span>
                        </div>
                        <div class="reviews__author-moto">
                            <span class="reviews__moto-label">Moto trasportata:</span>
                            <a href="#" class="reviews__moto-link">Ducati Panigale V4</a>
                        </div>
                    </div>
                </div>

                <div class="reviews__card">
                    <div class="reviews__card-header">
                        <div class="reviews__stars">
                            <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
                        </div>
                        <i class="fa-solid fa-quote-right reviews__quote-icon"></i>
                    </div>
                    <p class="reviews__text">"Ho trasportato la mia Harley per oltre 900km e non potevo chiedere di meglio. Puntualissimi e professionali. Userò ancora il servizio."</p>
                    <div class="reviews__author">
                        <div class="reviews__author-info">
                            <strong class="reviews__author-name">Luca Bianchi</strong>
                            <span class="reviews__author-route">Torino &rarr; Napoli</span>
                        </div>
                        <div class="reviews__author-moto">
                            <span class="reviews__moto-label">Moto trasportata:</span>
                            <a href="#" class="reviews__moto-link">Harley Davidson Sportster</a>
                        </div>
                    </div>
                </div>

                <div class="reviews__card">
                    <div class="reviews__card-header">
                        <div class="reviews__stars">
                            <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
                        </div>
                        <i class="fa-solid fa-quote-right reviews__quote-icon"></i>
                    </div>
                    <p class="reviews__text">"Ero preoccupato per la distanza ma il team mi ha seguito passo dopo passo. Moto consegnata come nuova. Prezzi onesti e trasparenti."</p>
                    <div class="reviews__author">
                        <div class="reviews__author-info">
                            <strong class="reviews__author-name">Alessandro Conti</strong>
                            <span class="reviews__author-route">Firenze &rarr; Palermo</span>
                        </div>
                        <div class="reviews__author-moto">
                            <span class="reviews__moto-label">Moto trasportata:</span>
                            <a href="#" class="reviews__moto-link">BMW R1250GS</a>
                        </div>
                    </div>
                </div>

                <div class="reviews__card">
                    <div class="reviews__card-header">
                        <div class="reviews__stars">
                            <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
                        </div>
                        <i class="fa-solid fa-quote-right reviews__quote-icon"></i>
                    </div>
                    <p class="reviews__text">"Finalmente un servizio serio! Ritiro puntuale, moto imballata con cura e consegna anticipata. Il tracking online è molto comodo."</p>
                    <div class="reviews__author">
                        <div class="reviews__author-info">
                            <strong class="reviews__author-name">Giulia Ferrari</strong>
                            <span class="reviews__author-route">Bologna &rarr; Venezia</span>
                        </div>
                        <div class="reviews__author-moto">
                            <span class="reviews__moto-label">Moto trasportata:</span>
                            <a href="#" class="reviews__moto-link">Yamaha MT-07</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats -->
            <div class="reviews__stats">
                <div class="reviews__stat">
                    <strong class="reviews__stat-number">5000+</strong>
                    <span class="reviews__stat-label">Trasporti Completati</span>
                </div>
                <div class="reviews__stat">
                    <strong class="reviews__stat-number">4.9/5</strong>
                    <span class="reviews__stat-label">Valutazione Media</span>
                </div>
                <div class="reviews__stat">
                    <strong class="reviews__stat-number">98%</strong>
                    <span class="reviews__stat-label">Clienti Soddisfatti</span>
                </div>
                <div class="reviews__stat">
                    <strong class="reviews__stat-number">10+</strong>
                    <span class="reviews__stat-label">Anni di Esperienza</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Chi Siamo Section -->
    <section class="about" id="chi-siamo">
        <div class="about__container">
            <div class="about__content">
                <div class="about__badge">La Nostra Azienda</div>
                <h2 class="about__title">Chi Siamo</h2>
                <p class="about__text">
                    Siamo un team di appassionati di moto con oltre 10 anni di esperienza nel trasporto veicoli. La nostra azienda nasce dalla volontà di offrire un servizio di trasporto moto professionale, sicuro e affidabile in tutta Italia.
                </p>
                <p class="about__text">
                    Ogni moto che trasportiamo viene trattata come se fosse la nostra. Utilizziamo mezzi specializzati, attrezzature di ultima generazione e sistemi di fissaggio certificati per garantire la massima sicurezza durante il trasporto.
                </p>

                <ul class="about__features">
                    <li class="about__feature">
                        <div class="about__feature-icon"><i class="fa-solid fa-users"></i></div>
                        <div class="about__feature-text">
                            <strong class="about__feature-title">Team Esperto</strong>
                            <span class="about__feature-description">Professionisti del settore con anni di esperienza nel trasporto moto.</span>
                        </div>
                    </li>
                    <li class="about__feature">
                        <div class="about__feature-icon"><i class="fa-solid fa-bullseye"></i></div>
                        <div class="about__feature-text">
                            <strong class="about__feature-title">Missione Chiara</strong>
                            <span class="about__feature-description">Rendere il trasporto moto semplice, sicuro e accessibile a tutti.</span>
                        </div>
                    </li>
                    <li class="about__feature">
                        <div class="about__feature-icon"><i class="fa-solid fa-heart"></i></div>
                        <div class="about__feature-text">
                            <strong class="about__feature-title">Passione</strong>
                            <span class="about__feature-description">Amiamo le moto quanto voi e le trattiamo con la massima cura.</span>
                        </div>
                    </li>
                </ul>
            </div>

            <div class="about__stats">
                <div class="about__stat-card">
                    <div class="about__stat-number">10+</div>
                    <div class="about__stat-label">Anni di Esperienza</div>
                </div>
                <div class="about__stat-card">
                    <div class="about__stat-number">5000+</div>
                    <div class="about__stat-label">Moto Trasportate</div>
                </div>
                <div class="about__stat-card">
                    <div class="about__stat-number">20+</div>
                    <div class="about__stat-label">Regioni Coperte</div>
                </div>
                <div class="about__stat-card">
                    <div class="about__stat-number">24/7</div>
                    <div class="about__stat-label">Supporto Clienti</div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Finale Section -->
    <section class="cta-final" id="preventivo">
        <div class="cta-final__container">
            <div class="cta-final__card">
                <h2 class="cta-final__title">Pronto a Trasportare la Tua Moto?</h2>
                <p class="cta-final__subtitle">Richiedi ora il tuo preventivo gratuito e personalizzato.</p>
                <div class="cta-final__buttons">
                    <a href="#" class="cta-btn cta-btn--dark open-quote-modal">Preventivo Gratuito &rarr;</a>
                    <a href="tel:<?= htmlspecialchars(env('COMPANY_PHONE_TEL', '+390000000000'), ENT_QUOTES, 'UTF-8') ?>" class="cta-btn cta-btn--white">Chiama Ora <i class="fa-solid fa-phone"></i></a>
                </div>
            </div>
        </div>
    </section>

    <?php include __DIR__ . '/../includes/footer.php'; ?>

    <?php include __DIR__ . '/../includes/quote-modal.php'; ?>

    <script src="/js/modules/nav.js"></script>
    <script src="/js/modules/gallery.js"></script>
    <script src="/js/modules/forms.js"></script>
    <?php if ($quoteUserData): ?>
        <script>
            window.QUOTE_USER_DATA = <?= json_encode($quoteUserData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
            window.QUOTE_USER_MOTORCYCLES = <?= json_encode($quoteUserMotos, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
        </script>
    <?php endif; ?>
    <?php if ($stripePk): ?>
        <script>
            window.STRIPE_PUBLIC_KEY = '<?= $stripePk ?>';
        </script>
        <script src="https://js.stripe.com/v3/" defer></script>
    <?php endif; ?>
    <script src="/js/modules/quote-modal.js" defer></script>
    <script src="/js/modules/google-maps.js"></script>
    <?php if ($gmapsKey): ?>
        <script src="https://maps.googleapis.com/maps/api/js?key=<?= $gmapsKey ?>&libraries=places,geometry&callback=initGoogleMaps" async defer></script>
    <?php endif; ?>
    <?php include __DIR__ . '/../includes/whatsapp-button.php'; ?>
</body>

</html>