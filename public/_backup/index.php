<?php
require_once __DIR__ . '/../src/auth.php';
$config = require __DIR__ . '/../src/config.php';
$user = isLogged() ? getCurrentUser() : null;
$gmapsKey = htmlspecialchars($config['google_maps_api_key'] ?? '', ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Starter Kit - Home</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="nav-container">
            <!-- Logo -->
            <div class="nav-logo">
                <a href="index.php">
                    <h2>MotoTransport</h2>
                </a>
            </div>

            <!-- Menu Desktop -->
            <div class="nav-menu">
                <a href="#come-funziona" data-section="come-funziona">Come funziona</a>
                <a href="#vantaggi" data-section="vantaggi">Vantaggi</a>
                <a href="#gallery" data-section="gallery">Gallery</a>
                <a href="#recensioni" data-section="recensioni">Recensioni</a>
                <a href="#chi-siamo" data-section="chi-siamo">Chi siamo</a>
            </div>

            <!-- Preventivo Button Desktop -->
            <div class="nav-cta-desktop">
                <a href="#" class="nav-cta-btn open-quote-modal">Preventivo Gratuito</a>
            </div>

            <!-- Mobile Controls -->
            <div class="nav-mobile-controls">
                <!-- Preventivo Button Mobile -->
                <a href="#" class="nav-cta-btn btn--mobile-cta open-quote-modal">Preventivo Gratuito</a>

                <!-- Hamburger Menu -->
                <button class="hamburger-menu" id="hamburgerMenu" aria-label="Menu">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>
        </div>

        <!-- Mobile Menu Overlay -->
        <div class="mobile-menu-overlay" id="mobileMenuOverlay"></div>

        <!-- Mobile Menu Sidebar -->
        <div class="mobile-menu" id="mobileMenu">
            <div class="mobile-menu-header">
                <h3>Menu</h3>
                <button class="mobile-menu-close" id="mobileMenuClose">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
            <div class="mobile-menu-content">
                <a href="#come-funziona">Come funziona</a>
                <a href="#vantaggi">Vantaggi</a>
                <a href="#gallery">Gallery</a>
                <a href="#recensioni">Recensioni</a>
                <a href="#chi-siamo">Chi siamo</a>
                <?php if ($user): ?>
                    <hr>
                    <a href="dashboard.php">Il Mio Profilo</a>
                    <a href="dashboard.php?section=orders">I Miei Ordini</a>
                    <?php if (isAdmin()): ?>
                        <a href="admin.php">Pannello Admin</a>
                    <?php endif; ?>
                    <a href="logout.php">Logout</a>
                <?php else: ?>
                    <hr>
                    <a href="login.php">Accedi</a>
                    <a href="register.php">Registrati</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

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
                    <div class="why-choose-us__card-icon">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                    <h3 class="why-choose-us__card-title">Assicurazione Totale</h3>
                    <p class="why-choose-us__card-description">
                        Copertura assicurativa completa per ogni trasporto. La tua moto è protetta dal ritiro alla consegna.
                    </p>
                </div>

                <div class="why-choose-us__card">
                    <div class="why-choose-us__card-icon">
                        <i class="fa-regular fa-clock"></i>
                    </div>
                    <h3 class="why-choose-us__card-title">Consegna Rapida</h3>
                    <p class="why-choose-us__card-description">
                        Tempi di consegna da 24 a 72 ore in tutta Italia. Rispettiamo sempre le tempistiche concordate.
                    </p>
                </div>

                <div class="why-choose-us__card">
                    <div class="why-choose-us__card-icon">
                        <i class="fa-solid fa-location-dot"></i>
                    </div>
                    <h3 class="why-choose-us__card-title">Tracking in Tempo Reale</h3>
                    <p class="why-choose-us__card-description">
                        Monitora la posizione della tua moto durante tutto il trasporto tramite la nostra piattaforma.
                    </p>
                </div>

                <div class="why-choose-us__card">
                    <div class="why-choose-us__card-icon">
                        <i class="fa-solid fa-headset"></i>
                    </div>
                    <h3 class="why-choose-us__card-title">Supporto 24/7</h3>
                    <p class="why-choose-us__card-description">
                        Team dedicato disponibile 24 ore su 24 per qualsiasi esigenza o informazione sul trasporto.
                    </p>
                </div>

                <div class="why-choose-us__card">
                    <div class="why-choose-us__card-icon">
                        <i class="fa-solid fa-euro-sign"></i>
                    </div>
                    <h3 class="why-choose-us__card-title">Prezzi Trasparenti</h3>
                    <p class="why-choose-us__card-description">
                        Preventivo chiaro e dettagliato senza costi nascosti. Paghi esattamente quanto concordato.
                    </p>
                </div>

                <div class="why-choose-us__card">
                    <div class="why-choose-us__card-icon">
                        <i class="fa-solid fa-medal"></i>
                    </div>
                    <h3 class="why-choose-us__card-title">Esperienza Decennale</h3>
                    <p class="why-choose-us__card-description">
                        Oltre 10 anni di esperienza nel settore con migliaia di trasporti completati con successo.
                    </p>
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
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                        </div>
                        <i class="fa-solid fa-quote-right reviews__quote-icon"></i>
                    </div>
                    <p class="reviews__text">
                        "Servizio impeccabile! La mia Ducati è arrivata in perfette condizioni. Comunicazione eccellente durante tutto il trasporto. Consigliato!"
                    </p>
                    <div class="reviews__author">
                        <div class="reviews__author-info">
                            <strong class="reviews__author-name">Marco Rossi</strong>
                            <span class="reviews__author-route">Milano → Roma</span>
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
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                        </div>
                        <i class="fa-solid fa-quote-right reviews__quote-icon"></i>
                    </div>
                    <p class="reviews__text">
                        "Ho trasportato la mia Harley per oltre 900km e non potevo chiedere di meglio. Puntualissimi e professionali. Userò ancora il servizio."
                    </p>
                    <div class="reviews__author">
                        <div class="reviews__author-info">
                            <strong class="reviews__author-name">Luca Bianchi</strong>
                            <span class="reviews__author-route">Torino → Napoli</span>
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
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                        </div>
                        <i class="fa-solid fa-quote-right reviews__quote-icon"></i>
                    </div>
                    <p class="reviews__text">
                        "Ero preoccupato per la distanza ma il team mi ha seguito passo dopo passo. Moto consegnata come nuova. Prezzi onesti e trasparenti."
                    </p>
                    <div class="reviews__author">
                        <div class="reviews__author-info">
                            <strong class="reviews__author-name">Alessandro Conti</strong>
                            <span class="reviews__author-route">Firenze → Palermo</span>
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
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                        </div>
                        <i class="fa-solid fa-quote-right reviews__quote-icon"></i>
                    </div>
                    <p class="reviews__text">
                        "Finalmente un servizio serio! Ritiro puntuale, moto imballata con cura e consegna anticipata. Il tracking online è molto comodo."
                    </p>
                    <div class="reviews__author">
                        <div class="reviews__author-info">
                            <strong class="reviews__author-name">Giulia Ferrari</strong>
                            <span class="reviews__author-route">Bologna → Venezia</span>
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
                        <div class="about__feature-icon">
                            <i class="fa-solid fa-users"></i>
                        </div>
                        <div class="about__feature-text">
                            <strong class="about__feature-title">Team Esperto</strong>
                            <span class="about__feature-description">Professionisti del settore con anni di esperienza nel trasporto moto.</span>
                        </div>
                    </li>
                    <li class="about__feature">
                        <div class="about__feature-icon">
                            <i class="fa-solid fa-bullseye"></i>
                        </div>
                        <div class="about__feature-text">
                            <strong class="about__feature-title">Missione Chiara</strong>
                            <span class="about__feature-description">Rendere il trasporto moto semplice, sicuro e accessibile a tutti.</span>
                        </div>
                    </li>
                    <li class="about__feature">
                        <div class="about__feature-icon">
                            <i class="fa-solid fa-heart"></i>
                        </div>
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
                    <a href="tel:+390000000000" class="cta-btn cta-btn--white">Chiama Ora <i class="fa-solid fa-phone"></i></a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer__container">
            <div class="footer__grid">
                <!-- Company Info -->
                <div class="footer__column">
                    <div class="footer__logo-box"></div>
                    <p class="footer__description">
                        Il servizio di trasporto moto più affidabile d'Italia. Sicurezza, professionalità e puntualità garantite.
                    </p>
                </div>

                <!-- Link Rapidi -->
                <div class="footer__column">
                    <h4 class="footer__title">Link Rapidi</h4>
                    <ul class="footer__links">
                        <li><a href="#come-funziona">Come Funziona</a></li>
                        <li><a href="#vantaggi">Vantaggi</a></li>
                        <li><a href="#gallery">Gallery</a></li>
                        <li><a href="#recensioni">Recensioni</a></li>
                        <li><a href="#chi-siamo">Chi Siamo</a></li>
                    </ul>
                </div>

                <!-- Servizi -->
                <div class="footer__column">
                    <h4 class="footer__title">Servizi</h4>
                    <ul class="footer__links">
                        <li><a href="#">Trasporto Moto</a></li>
                        <li><a href="#">Trasporto Scooter</a></li>
                    </ul>
                </div>

                <!-- Contatti -->
                <div class="footer__column">
                    <h4 class="footer__title">Contatti</h4>
                    <ul class="footer__contacts">
                        <li>
                            <div class="footer__contact-icon">
                                <i class="fa-solid fa-location-dot"></i>
                            </div>
                            <span>Via Example 123, 20100 Milano (MI)</span>
                        </li>
                        <li>
                            <div class="footer__contact-icon">
                                <i class="fa-solid fa-phone"></i>
                            </div>
                            <span>+39 012 345 6789</span>
                        </li>
                        <li>
                            <div class="footer__contact-icon">
                                <i class="fa-solid fa-envelope"></i>
                            </div>
                            <span>info@mototransport.it</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="footer__bottom">
                <p class="footer__copyright">&copy; 2026 MotoTransport Italia. Tutti i diritti riservati.</p>
                <div class="footer__legal">
                    <a href="#">Privacy Policy</a>
                    <a href="#">Termini e Condizioni</a>
                    <a href="#">Cookie Policy</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- ===== MODALE PREVENTIVO MULTI-STEP ===== -->
    <div class="quote-overlay" id="quoteModal" aria-hidden="true">
        <div class="quote-modal" role="dialog" aria-modal="true" aria-labelledby="quoteModalTitle">

            <!-- Pulsante chiudi -->
            <button class="quote-modal__close" id="quoteModalClose" aria-label="Chiudi">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>

            <!-- Intestazione -->
            <div class="quote-modal__header">
                <h2 class="quote-modal__title" id="quoteModalTitle">Richiedi un Preventivo</h2>
            </div>

            <!-- Stepper -->
            <div class="quote-stepper" id="quoteStepper">
                <div class="quote-stepper__step active" data-step="1">
                    <div class="quote-stepper__circle">
                        <svg class="quote-stepper__icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M5 17H3a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11a2 2 0 0 1 2 2v3"></path>
                            <rect x="9" y="11" width="14" height="10" rx="2"></rect>
                            <circle cx="12" cy="20" r="1"></circle>
                            <circle cx="20" cy="20" r="1"></circle>
                        </svg>
                        <svg class="quote-stepper__check" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </div>
                    <span class="quote-stepper__label">Moto</span>
                </div>
                <div class="quote-stepper__line"></div>
                <div class="quote-stepper__step" data-step="2">
                    <div class="quote-stepper__circle">
                        <svg class="quote-stepper__icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                            <circle cx="12" cy="10" r="3"></circle>
                        </svg>
                        <svg class="quote-stepper__check" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </div>
                    <span class="quote-stepper__label">Tragitto</span>
                </div>
                <div class="quote-stepper__line"></div>
                <div class="quote-stepper__step" data-step="3">
                    <div class="quote-stepper__circle">
                        <svg class="quote-stepper__icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                        <svg class="quote-stepper__check" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </div>
                    <span class="quote-stepper__label">Data</span>
                </div>
                <div class="quote-stepper__line"></div>
                <div class="quote-stepper__step" data-step="4">
                    <div class="quote-stepper__circle">
                        <svg class="quote-stepper__icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                        <svg class="quote-stepper__check" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </div>
                    <span class="quote-stepper__label">Dati</span>
                </div>
                <div class="quote-stepper__line"></div>
                <div class="quote-stepper__step" data-step="5">
                    <div class="quote-stepper__circle">
                        <svg class="quote-stepper__icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                        </svg>
                        <svg class="quote-stepper__check" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </div>
                    <span class="quote-stepper__label">Riepilogo</span>
                </div>
            </div>

            <!-- Contenuto degli step -->
            <div class="quote-modal__body">

                <!-- STEP 1: Dettagli Moto -->
                <div class="quote-step" id="quoteStep1">
                    <h3 class="quote-step__title">Dettagli della moto</h3>
                    <div class="quote-form">
                        <div class="quote-form__row">
                            <div class="quote-form__group">
                                <label class="quote-form__label" for="motoBrand">Marca <span class="quote-form__required">*</span></label>
                                <input class="quote-form__input" type="text" id="motoBrand" name="motoBrand" placeholder="Es. Ducati">
                            </div>
                            <div class="quote-form__group">
                                <label class="quote-form__label" for="motoModel">Modello <span class="quote-form__required">*</span></label>
                                <input class="quote-form__input" type="text" id="motoModel" name="motoModel" placeholder="Es. Panigale V4">
                            </div>
                        </div>
                        <div class="quote-form__group">
                            <label class="quote-form__label" for="motoCc">Cilindrata <span class="quote-form__required">*</span></label>
                            <input class="quote-form__input" type="text" id="motoCc" name="motoCc" placeholder="Es. 1103cc">
                        </div>

                        <div class="quote-form__separator"></div>

                        <h4 class="quote-form__section-title">Servizi aggiuntivi a pagamento</h4>
                        <div class="quote-form__group">
                            <label class="quote-form__label quote-form__label--link" for="motoBags">La tua moto ha borse laterali?</label>
                            <div class="quote-form__select-wrapper">
                                <select class="quote-form__select" id="motoBags" name="motoBags">
                                    <option value="0">No, non ci sono borse laterali - €0</option>
                                    <option value="30">Sì, ma le faccio trovare smontate - €30</option>
                                    <option value="70">Sì, ma non sono smontabili - €70</option>
                                </select>
                                <svg class="quote-form__select-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="6 9 12 15 18 9"></polyline>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STEP 2: Tragitto -->
                <div class="quote-step quote-step--hidden" id="quoteStep2">
                    <h3 class="quote-step__title">Tragitto</h3>
                    <div class="quote-form">
                        <!-- Indirizzo di ritiro -->
                        <div class="quote-form__group">
                            <label class="quote-form__label" for="addressPickup">Indirizzo di ritiro <span class="quote-form__required">*</span></label>
                            <div class="quote-form__input-wrapper">
                                <input class="quote-form__input quote-form__input--with-icon" type="text" id="addressPickup" name="addressPickup" placeholder="Via pinco pallino 12, Milano, 20070" autocomplete="off">
                                <button type="button" class="quote-form__input-icon" id="pickupMapBtn" title="Seleziona sulla mappa">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <circle cx="12" cy="12" r="3"></circle>
                                        <line x1="12" y1="2" x2="12" y2="6"></line>
                                        <line x1="12" y1="18" x2="12" y2="22"></line>
                                        <line x1="2" y1="12" x2="6" y2="12"></line>
                                        <line x1="18" y1="12" x2="22" y2="12"></line>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <!-- Indirizzo di consegna -->
                        <div class="quote-form__group">
                            <label class="quote-form__label" for="addressDelivery">Indirizzo di consegna <span class="quote-form__required">*</span></label>
                            <div class="quote-form__input-wrapper">
                                <input class="quote-form__input quote-form__input--with-icon" type="text" id="addressDelivery" name="addressDelivery" placeholder="Via pinco pallino 12, Milano, 20070" autocomplete="off">
                                <button type="button" class="quote-form__input-icon" id="deliveryMapBtn" title="Seleziona sulla mappa">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <circle cx="12" cy="12" r="3"></circle>
                                        <line x1="12" y1="2" x2="12" y2="6"></line>
                                        <line x1="12" y1="18" x2="12" y2="22"></line>
                                        <line x1="2" y1="12" x2="6" y2="12"></line>
                                        <line x1="18" y1="12" x2="22" y2="12"></line>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Contenitore mappa per selezione indirizzo -->
                        <div class="quote-map-container" id="quoteMapContainer" style="display:none;">
                            <div class="quote-map__header">
                                <span class="quote-map__header-label" id="quoteMapLabel">Seleziona indirizzo di ritiro</span>
                                <button type="button" class="quote-map__close-btn" id="quoteMapCloseBtn" title="Chiudi mappa">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                        <line x1="18" y1="6" x2="6" y2="18"></line>
                                        <line x1="6" y1="6" x2="18" y2="18"></line>
                                    </svg>
                                </button>
                            </div>
                            <div class="quote-map" id="quoteMap"></div>
                            <!-- Pin/mirino fisso al centro della mappa -->
                            <div class="quote-map__pin" id="quoteMapPin">
                                <svg width="40" height="40" viewBox="0 0 40 40" fill="none">
                                    <circle cx="20" cy="20" r="6" fill="#0284c7" fill-opacity="0.2" stroke="#0284c7" stroke-width="2" />
                                    <circle cx="20" cy="20" r="2" fill="#0284c7" />
                                    <line x1="20" y1="6" x2="20" y2="14" stroke="#0284c7" stroke-width="2" stroke-linecap="round" />
                                    <line x1="20" y1="26" x2="20" y2="34" stroke="#0284c7" stroke-width="2" stroke-linecap="round" />
                                    <line x1="6" y1="20" x2="14" y2="20" stroke="#0284c7" stroke-width="2" stroke-linecap="round" />
                                    <line x1="26" y1="20" x2="34" y2="20" stroke="#0284c7" stroke-width="2" stroke-linecap="round" />
                                </svg>
                            </div>
                            <div class="quote-map__footer">
                                <p class="quote-map__address" id="quoteMapAddress">Sposta la mappa per selezionare l'indirizzo</p>
                                <button type="button" class="quote-btn quote-btn--next quote-map__confirm-btn" id="quoteMapConfirmBtn">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                        <polyline points="20 6 9 17 4 12"></polyline>
                                    </svg>
                                    Conferma posizione
                                </button>
                            </div>
                        </div>

                        <!-- Riepilogo tratta (appare dopo selezione di entrambi gli indirizzi) -->
                        <div class="quote-route-summary" id="routeSummary" style="display:none;">
                            <div class="quote-route-summary__map" id="routePreviewMap"></div>
                            <div class="quote-route-costs" id="routeCosts">
                                <div class="quote-route-costs__item">
                                    <span class="quote-route-costs__label">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z" />
                                        </svg>
                                        Distanza
                                    </span>
                                    <strong class="quote-route-costs__value" id="routeDistance">—</strong>
                                </div>
                                <div class="quote-route-costs__item">
                                    <span class="quote-route-costs__label">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <circle cx="12" cy="12" r="10" />
                                            <polyline points="12 6 12 12 16 14" />
                                        </svg>
                                        Durata stimata
                                    </span>
                                    <strong class="quote-route-costs__value" id="routeDuration">—</strong>
                                </div>
                                <div class="quote-route-costs__divider"></div>
                                <div class="quote-route-costs__item">
                                    <span class="quote-route-costs__label">Carburante (furgone benzina)</span>
                                    <strong class="quote-route-costs__value" id="routeFuelCost">—</strong>
                                </div>
                                <div class="quote-route-costs__item">
                                    <span class="quote-route-costs__label">Pedaggi autostradali</span>
                                    <strong class="quote-route-costs__value" id="routeTollCost">—</strong>
                                </div>
                                <div class="quote-route-costs__divider"></div>
                                <div class="quote-route-costs__item quote-route-costs__item--total">
                                    <span class="quote-route-costs__label">Costo trasporto</span>
                                    <strong class="quote-route-costs__value" id="routeTotalCost">—</strong>
                                </div>
                                <p class="quote-route-costs__note" id="routeMinNote" style="display:none;">* Spesa minima di €50 applicata</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STEP 3: Tipo di consegna -->
                <div class="quote-step quote-step--hidden" id="quoteStep3">
                    <h3 class="quote-step__title">Tipo di consegna</h3>
                    <div class="quote-form">
                        <p class="quote-form__label">Tipo di consegna <span class="quote-form__required">*</span></p>
                        <div class="quote-delivery-options" id="deliveryOptions">
                            <label class="quote-delivery-option quote-delivery-option--selected" id="optStandard">
                                <input type="radio" name="deliveryType" value="Standard" checked>
                                <div class="quote-delivery-option__content">
                                    <div class="quote-delivery-option__radio">
                                        <span class="quote-delivery-option__dot"></span>
                                    </div>
                                    <div class="quote-delivery-option__info">
                                        <span class="quote-delivery-option__name">Standard ( 6 - 7 giorni )</span>
                                    </div>
                                    <span class="quote-delivery-option__price">Gratis</span>
                                </div>
                            </label>
                            <label class="quote-delivery-option" id="optExpress">
                                <input type="radio" name="deliveryType" value="Express">
                                <div class="quote-delivery-option__content">
                                    <div class="quote-delivery-option__radio">
                                        <span class="quote-delivery-option__dot"></span>
                                    </div>
                                    <div class="quote-delivery-option__info">
                                        <span class="quote-delivery-option__name">Express ( 3 - 5 giorni )</span>
                                    </div>
                                    <span class="quote-delivery-option__price">+50€</span>
                                </div>
                            </label>
                            <label class="quote-delivery-option" id="optUrgent">
                                <input type="radio" name="deliveryType" value="Urgente">
                                <div class="quote-delivery-option__content">
                                    <div class="quote-delivery-option__radio">
                                        <span class="quote-delivery-option__dot"></span>
                                    </div>
                                    <div class="quote-delivery-option__info">
                                        <span class="quote-delivery-option__name">Urgente ( 24 - 48h )</span>
                                    </div>
                                    <span class="quote-delivery-option__price">+100€</span>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- STEP 4: Dati cliente -->
                <div class="quote-step quote-step--hidden" id="quoteStep4">
                    <h3 class="quote-step__title">I Tuoi Dati</h3>
                    <div class="quote-form">
                        <div class="quote-form__row">
                            <div class="quote-form__group">
                                <label class="quote-form__label" for="clientName">Nome e Cognome <span class="quote-form__required">*</span></label>
                                <input class="quote-form__input" type="text" id="clientName" name="clientName" placeholder="Es. Mario Rossi">
                            </div>
                            <div class="quote-form__group">
                                <label class="quote-form__label" for="clientEmail">Email <span class="quote-form__required">*</span></label>
                                <input class="quote-form__input" type="email" id="clientEmail" name="clientEmail" placeholder="Es. email@gmail.com">
                            </div>
                        </div>
                        <div class="quote-form__row">
                            <div class="quote-form__group">
                                <label class="quote-form__label" for="clientPhone">Numero di telefono <span class="quote-form__required">*</span></label>
                                <input class="quote-form__input" type="tel" id="clientPhone" name="clientPhone" placeholder="Es. 3285449887">
                            </div>
                            <div class="quote-form__group">
                                <label class="quote-form__label" for="clientFiscal">Codice Fiscale <span class="quote-form__required">*</span></label>
                                <input class="quote-form__input" type="text" id="clientFiscal" name="clientFiscal" placeholder="Es. YEGDTRSGUQEGP96Z" style="text-transform:uppercase;">
                            </div>
                        </div>
                        <div class="quote-form__group">
                            <label class="quote-form__checkbox">
                                <input type="checkbox" id="privacyAccept" name="privacyAccept">
                                <span class="quote-form__checkbox-box"></span>
                                <span class="quote-form__checkbox-label">Accetto i <a href="#" class="quote-form__link">Termini e Condizioni</a> e la <a href="#" class="quote-form__link">Privacy Policy</a> <span class="quote-form__required">*</span></span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- STEP 5: Riepilogo -->
                <div class="quote-step quote-step--hidden" id="quoteStep5">
                    <h3 class="quote-step__title">Riepilogo Preventivo</h3>
                    <div class="quote-summary">
                        <div class="quote-summary__card">
                            <div class="quote-summary__row">
                                <div class="quote-summary__info">
                                    <span class="quote-summary__sublabel">Moto</span>
                                    <strong class="quote-summary__value" id="summaryMoto">—</strong>
                                    <span class="quote-summary__desc" id="summaryMotoType">—</span>
                                </div>
                                <svg class="quote-summary__icon" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path d="M5 17H3a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11a2 2 0 0 1 2 2v3"></path>
                                    <rect x="9" y="11" width="14" height="10" rx="2"></rect>
                                    <circle cx="12" cy="20" r="1"></circle>
                                    <circle cx="20" cy="20" r="1"></circle>
                                </svg>
                            </div>
                            <div class="quote-summary__divider"></div>
                            <div class="quote-summary__row">
                                <div class="quote-summary__info">
                                    <span class="quote-summary__sublabel">Tragitto</span>
                                    <strong class="quote-summary__value" id="summaryRoute">—</strong>
                                </div>
                                <svg class="quote-summary__icon" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                    <circle cx="12" cy="10" r="3"></circle>
                                </svg>
                            </div>
                            <div class="quote-summary__divider"></div>
                            <div class="quote-summary__row">
                                <div class="quote-summary__info">
                                    <span class="quote-summary__sublabel">Tipo di Ritiro</span>
                                    <strong class="quote-summary__value" id="summaryDelivery">—</strong>
                                    <span class="quote-summary__desc" id="summaryDeliveryDesc">—</span>
                                </div>
                                <svg class="quote-summary__icon" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                    <line x1="16" y1="2" x2="16" y2="6"></line>
                                    <line x1="8" y1="2" x2="8" y2="6"></line>
                                    <line x1="3" y1="10" x2="21" y2="10"></line>
                                </svg>
                            </div>
                            <div class="quote-summary__divider"></div>
                            <div class="quote-summary__row">
                                <div class="quote-summary__info">
                                    <span class="quote-summary__sublabel">Cliente</span>
                                    <strong class="quote-summary__value" id="summaryName">—</strong>
                                    <span class="quote-summary__desc" id="summaryContact">—</span>
                                </div>
                                <svg class="quote-summary__icon" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                            </div>
                        </div>

                        <div class="quote-summary__price-card">
                            <span class="quote-summary__price-label">Prezzo Totale</span>
                            <strong class="quote-summary__price-value" id="summaryPrice">—</strong>
                            <span class="quote-summary__price-note">IVA inclusa</span>
                        </div>

                        <p class="quote-summary__disclaimer">Cliccando "Conferma e Paga" verrai reindirizzato al sistema di pagamento sicuro.</p>
                    </div>
                </div>

            </div><!-- fine modal body -->

            <!-- Footer navigazione -->
            <div class="quote-modal__footer" id="quoteModalFooter">
                <button class="quote-btn quote-btn--back" id="quotePrevBtn" style="display:none;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <polyline points="15 18 9 12 15 6"></polyline>
                    </svg>
                    Indietro
                </button>
                <button class="quote-btn quote-btn--next" id="quoteNextBtn">
                    Continua
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </button>
                <button class="quote-btn quote-btn--confirm" id="quoteConfirmBtn" style="display:none;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="1" y="4" width="22" height="16" rx="2"></rect>
                        <line x1="1" y1="10" x2="23" y2="10"></line>
                    </svg>
                    Conferma e paga
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </button>
            </div>

        </div>
    </div>

    <script src="js/main.js"></script>
    <?php if ($gmapsKey): ?>
        <script src="https://maps.googleapis.com/maps/api/js?key=<?= $gmapsKey ?>&libraries=places,geometry&callback=initGoogleMaps" async defer></script>
    <?php endif; ?>
</body>

</html>