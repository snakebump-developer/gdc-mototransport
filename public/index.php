<?php
require_once __DIR__ . '/../src/auth.php';
$user = isLogged() ? getCurrentUser() : null;
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
                <a href="#home">Come funziona</a>
                <a href="#features">Vantaggi</a>
                <a href="#pricing">Gallery</a>
                <a href="#reviews">Recensioni</a>
                <a href="#contact">Chi siamo</a>
            </div>

            <!-- Preventivo Button Desktop -->
            <div class="nav-cta-desktop">
                <a href="#" class="btn btn-primary open-quote-modal">Preventivo Gratuito</a>
            </div>

            <!-- Mobile Controls -->
            <div class="nav-mobile-controls">
                <!-- Preventivo Button Mobile -->
                <a href="#" class="btn btn-primary btn-mobile-cta open-quote-modal">Preventivo Gratuito</a>

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
                <a href="#home">Come funziona</a>
                <a href="#features">Vantaggi</a>
                <a href="#pricing">Gallery</a>
                <a href="#reviews">Recensioni</a>
                <a href="#contact">Chi siamo</a>
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
            <h2 class="reviews__title">Recensioni Clienti</h2>
            <p class="reviews__subtitle">La soddisfazione dei nostri clienti è la nostra migliore pubblicità. Ecco cosa dicono di noi.</p>

            <div class="reviews__grid">
                <div class="reviews__card">
                    <div class="reviews__stars">
                        <span class="reviews__star reviews__star--filled">★</span>
                        <span class="reviews__star reviews__star--filled">★</span>
                        <span class="reviews__star reviews__star--filled">★</span>
                        <span class="reviews__star reviews__star--filled">★</span>
                        <span class="reviews__star reviews__star--filled">★</span>
                    </div>
                    <p class="reviews__text">
                        "Servizio impeccabile! La mia Ducati è arrivata in perfette condizioni. Professionali e puntuali, super consigliati!"
                    </p>
                    <div class="reviews__author">
                        <strong class="reviews__author-name">Marco Rossi</strong>
                        <span class="reviews__author-role">Privato · Roma</span>
                    </div>
                </div>

                <div class="reviews__card">
                    <div class="reviews__stars">
                        <span class="reviews__star reviews__star--filled">★</span>
                        <span class="reviews__star reviews__star--filled">★</span>
                        <span class="reviews__star reviews__star--filled">★</span>
                        <span class="reviews__star reviews__star--filled">★</span>
                        <span class="reviews__star reviews__star--filled">★</span>
                    </div>
                    <p class="reviews__text">
                        "Ho trasportato la mia moto da Milano a Palermo. Tutto tracking è stato perfetto, molto professionali. Grazie ancora!"
                    </p>
                    <div class="reviews__author">
                        <strong class="reviews__author-name">Luca Bianchi</strong>
                        <span class="reviews__author-role">Appassionato · Milano</span>
                    </div>
                </div>

                <div class="reviews__card">
                    <div class="reviews__stars">
                        <span class="reviews__star reviews__star--filled">★</span>
                        <span class="reviews__star reviews__star--filled">★</span>
                        <span class="reviews__star reviews__star--filled">★</span>
                        <span class="reviews__star reviews__star--filled">★</span>
                        <span class="reviews__star reviews__star--filled">★</span>
                    </div>
                    <p class="reviews__text">
                        "Che professionalità per la distanza mia il hanno fatto molto veloci. Moto arrivata intatta. Molto sodisfatto!"
                    </p>
                    <div class="reviews__author">
                        <strong class="reviews__author-name">Alessandro Conti</strong>
                        <span class="reviews__author-role">Moto concessionario</span>
                    </div>
                </div>

                <div class="reviews__card">
                    <div class="reviews__stars">
                        <span class="reviews__star reviews__star--filled">★</span>
                        <span class="reviews__star reviews__star--filled">★</span>
                        <span class="reviews__star reviews__star--filled">★</span>
                        <span class="reviews__star reviews__star--filled">★</span>
                        <span class="reviews__star reviews__star--filled">★</span>
                    </div>
                    <p class="reviews__text">
                        "Finalmente un servizio serio! Prezzi trasparenti, nessun costo nascosto. Il tracking in tempo reale è geniale. 5 stelle!"
                    </p>
                    <div class="reviews__author">
                        <strong class="reviews__author-name">Giulia Martini</strong>
                        <span class="reviews__author-role">Moto viaggiatore</span>
                    </div>
                </div>
            </div>

            <!-- Stats -->
            <div class="reviews__stats">
                <div class="reviews__stat">
                    <strong class="reviews__stat-number">5000+</strong>
                    <span class="reviews__stat-label">Trasporti Effettuati</span>
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
                    <strong class="reviews__stat-number">15+</strong>
                    <span class="reviews__stat-label">Anni di Esperienza</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Chi Siamo Section -->
    <section class="about" id="chi-siamo">
        <div class="about__container">
            <div class="about__content">
                <h2 class="about__title">Chi Siamo</h2>
                <p class="about__text">
                    Siamo un team di professionisti specializzati nel trasporto di veicoli a due ruote con anni di esperienza nel settore. La nostra missione è offrire un servizio di eccellenza, garantendo la massima sicurezza e affidabilità in ogni spedizione.
                </p>
                <p class="about__text">
                    Utilizziamo solo mezzi certificati e assicurati, con personale altamente qualificato e formato per gestire ogni tipo di moto, dalle piccole cilindrate alle moto custom di alto valore. Il nostro impegno quotidiano è la soddisfazione completa di ogni cliente.
                </p>

                <ul class="about__features">
                    <li class="about__feature">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                        </svg>
                        <span><strong>Team Esperto:</strong> Autisti certificati con esperienza nel trasporto motocicli</span>
                    </li>
                    <li class="about__feature">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                        </svg>
                        <span><strong>Sicurezza e copertura totale:</strong> Assicurazione completa, veicoli e GPS</span>
                    </li>
                    <li class="about__feature">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                        </svg>
                        <span><strong>Trasparenza:</strong> Preventivi chiari senza costi nascosti, tracciamento in tempo reale</span>
                    </li>
                </ul>
            </div>

            <div class="about__stats">
                <div class="about__stat-card">
                    <div class="about__stat-number">15+</div>
                    <div class="about__stat-label">Anni di Esperienza</div>
                </div>
                <div class="about__stat-card">
                    <div class="about__stat-number">5000+</div>
                    <div class="about__stat-label">Moto Trasportate</div>
                </div>
                <div class="about__stat-card">
                    <div class="about__stat-number">25+</div>
                    <div class="about__stat-label">Città Servite</div>
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
            <h2 class="cta-final__title">Pronto a Trasportare la Tua Moto?</h2>
            <p class="cta-final__subtitle">Richiedi subito un preventivo gratuito e senza impegno. Ti ricontattiamo in pochi minuti!</p>

            <div class="cta-final__buttons">
                <a href="#" class="btn btn--dark btn--large open-quote-modal">Preventivo Gratuito</a>
                <a href="#come-funziona" class="btn btn--white btn--large">Inizia Ora</a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer__container">
            <div class="footer__grid">
                <!-- Company Info -->
                <div class="footer__column">
                    <h3 class="footer__logo">MotoTransport</h3>
                    <p class="footer__description">
                        Il servizio di trasporto moto più affidabile e professionale in Italia. Sicurezza e puntualità garantita.
                    </p>
                </div>

                <!-- Link Rapidi -->
                <div class="footer__column">
                    <h4 class="footer__title">Link Rapidi</h4>
                    <ul class="footer__links">
                        <li><a href="#come-funziona">Come Funziona</a></li>
                        <li><a href="#vantaggi">I Vantaggi</a></li>
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
                        <li><a href="#">Tracking GPS</a></li>
                        <li><a href="#">Assicurazione</a></li>
                    </ul>
                </div>

                <!-- Contatti -->
                <div class="footer__column">
                    <h4 class="footer__title">Contatti</h4>
                    <ul class="footer__contacts">
                        <li>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                <circle cx="12" cy="10" r="3"></circle>
                            </svg>
                            Via Esempio 123, 20100 Milano
                        </li>
                        <li>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                            </svg>
                            +39 02 345 6789
                        </li>
                        <li>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                <polyline points="22,6 12,13 2,6"></polyline>
                            </svg>
                            info@mototransport.it
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
                        <div class="quote-form__group">
                            <label class="quote-form__label" for="motoType">Tipo di moto <span class="quote-form__required">*</span></label>
                            <div class="quote-form__select-wrapper">
                                <select class="quote-form__select" id="motoType" name="motoType">
                                    <option value="">Seleziona tipo</option>
                                    <option value="Naked/Roadster">Naked / Roadster</option>
                                    <option value="Sportiva">Sportiva</option>
                                    <option value="Touring/Gran Turismo">Touring / Gran Turismo</option>
                                    <option value="Enduro/Adventure">Enduro / Adventure</option>
                                    <option value="Custom/Cruiser">Custom / Cruiser</option>
                                    <option value="Scooter">Scooter</option>
                                </select>
                                <svg class="quote-form__select-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="6 9 12 15 18 9"></polyline>
                                </svg>
                            </div>
                        </div>
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
                    </div>
                </div>

                <!-- STEP 2: Tragitto -->
                <div class="quote-step quote-step--hidden" id="quoteStep2">
                    <h3 class="quote-step__title">Tragitto</h3>
                    <div class="quote-form">
                        <div class="quote-form__group">
                            <label class="quote-form__label" for="addressPickup">Indirizzo di ritiro <span class="quote-form__required">*</span></label>
                            <input class="quote-form__input" type="text" id="addressPickup" name="addressPickup" placeholder="Via Pino pallino 12, Milano, 20070">
                        </div>
                        <div class="quote-form__group">
                            <label class="quote-form__label" for="addressDelivery">Indirizzo di consegna <span class="quote-form__required">*</span></label>
                            <input class="quote-form__input" type="text" id="addressDelivery" name="addressDelivery" placeholder="Via Pino pallino 12, Milano, 20070">
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
</body>

</html>