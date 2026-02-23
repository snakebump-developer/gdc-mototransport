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
                <a href="#preventivo" class="btn btn-primary">Preventivo Gratuito</a>
            </div>

            <!-- Mobile Controls -->
            <div class="nav-mobile-controls">
                <!-- Preventivo Button Mobile -->
                <a href="#preventivo" class="btn btn-primary btn-mobile-cta">Preventivo Gratuito</a>

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
                    Trasportiamo la tua moto in <span class="hero__title--highlight">tutta sicurezza</span>
                </h1>
                <p class="hero__subtitle">
                    Servizio professionale di trasporto moto ovunque in tutta Italia.
                    Veloci e sicuri con un servizio completamente garantito a un prezzo super conveniente.
                </p>
                <div class="hero__buttons">
                    <a href="#preventivo" class="btn btn--primary btn--large">Richiedi preventivo</a>
                    <a href="#contact" class="btn btn--secondary btn--large">Invia Foto</a>
                </div>

                <!-- Hero Features -->
                <div class="hero__features">
                    <div class="hero__feature">
                        <div class="hero__feature-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                            </svg>
                        </div>
                        <div class="hero__feature-text">
                            <strong>100%</strong>
                            <span>Sicuro</span>
                        </div>
                    </div>
                    <div class="hero__feature">
                        <div class="hero__feature-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12 6 12 12 16 14"></polyline>
                            </svg>
                        </div>
                        <div class="hero__feature-text">
                            <strong>24/7</strong>
                            <span>Veloce</span>
                        </div>
                    </div>
                    <div class="hero__feature">
                        <div class="hero__feature-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                <circle cx="12" cy="10" r="3"></circle>
                            </svg>
                        </div>
                        <div class="hero__feature-text">
                            <strong>GPS</strong>
                            <span>Tracciato</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Come Funziona Section -->
    <section class="how-it-works" id="come-funziona">
        <div class="how-it-works__container">
            <h2 class="how-it-works__title">Come Funziona</h2>
            <p class="how-it-works__subtitle">Trasportare la tua moto non è mai stato così facile. Tre semplici passi per un servizio impeccabile.</p>
            
            <div class="how-it-works__grid">
                <div class="how-it-works__card">
                    <div class="how-it-works__card-icon">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                            <polyline points="10 9 9 9 8 9"></polyline>
                        </svg>
                    </div>
                    <h3 class="how-it-works__card-title">Richiedi Preventivo</h3>
                    <p class="how-it-works__card-description">
                        Compila il nostro semplice modulo oppure inviaci le foto della tua moto. Ricevi un preventivo immediato e senza impegno in pochi minuti.
                    </p>
                </div>

                <div class="how-it-works__card">
                    <div class="how-it-works__card-icon">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="1" y="3" width="15" height="13"></rect>
                            <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
                            <circle cx="5.5" cy="18.5" r="2.5"></circle>
                            <circle cx="18.5" cy="18.5" r="2.5"></circle>
                        </svg>
                    </div>
                    <h3 class="how-it-works__card-title">Ritiro e Consegna</h3>
                    <p class="how-it-works__card-description">
                        I nostri esperti ritirano la tua moto nel luogo e orario concordato. Utilizziamo solo mezzi certificati e assicurati per il massimo della sicurezza.
                    </p>
                </div>

                <div class="how-it-works__card">
                    <div class="how-it-works__card-icon">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                        </svg>
                    </div>
                    <h3 class="how-it-works__card-title">Consegna Garantita</h3>
                    <p class="how-it-works__card-description">
                        Monitora la spedizione in tempo reale e ricevi la tua moto nella destinazione concordata in perfette condizioni e documentazione completa.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Perché Sceglierci Section -->
    <section class="why-choose-us" id="vantaggi">
        <div class="why-choose-us__container">
            <h2 class="why-choose-us__title">Perché Sceglierci</h2>
            <p class="why-choose-us__subtitle">
                Affidati solo ai migliori per la cura del tuo veicolo. 
                Anni di esperienza e migliaia di clienti soddisfatti ci rendono il partner ideale.
            </p>

            <div class="why-choose-us__grid">
                <div class="why-choose-us__card">
                    <div class="why-choose-us__card-icon">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                        </svg>
                    </div>
                    <h3 class="why-choose-us__card-title">Assicurazione Totale</h3>
                    <p class="why-choose-us__card-description">
                        Copertura assicurativa completa per ogni spedizione. La tua moto è sempre protetta da eventuali danni.
                    </p>
                </div>

                <div class="why-choose-us__card">
                    <div class="why-choose-us__card-icon">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="16 18 22 12 16 6"></polyline>
                            <polyline points="8 6 2 12 8 18"></polyline>
                        </svg>
                    </div>
                    <h3 class="why-choose-us__card-title">Consegna Rapida</h3>
                    <p class="why-choose-us__card-description">
                        Tempi di consegna da 24 a 72 ore in tutta Italia. Servizi express disponibili su richiesta per urgenze.
                    </p>
                </div>

                <div class="why-choose-us__card">
                    <div class="why-choose-us__card-icon">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                            <circle cx="12" cy="10" r="3"></circle>
                        </svg>
                    </div>
                    <h3 class="why-choose-us__card-title">Tracking in Tempo Reale</h3>
                    <p class="why-choose-us__card-description">
                        Monitora la spedizione della tua moto in tempo reale. Ricevi aggiornamenti costanti sulla posizione del veicolo.
                    </p>
                </div>

                <div class="why-choose-us__card">
                    <div class="why-choose-us__card-icon">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                    </div>
                    <h3 class="why-choose-us__card-title">Team di Esperti</h3>
                    <p class="why-choose-us__card-description">
                        Professionisti certificati e formati con anni di esperienza nel settore del trasporto veicoli a due ruote.
                    </p>
                </div>

                <div class="why-choose-us__card">
                    <div class="why-choose-us__card-icon">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="12" y1="1" x2="12" y2="23"></line>
                            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                        </svg>
                    </div>
                    <h3 class="why-choose-us__card-title">Prezzi Trasparenti</h3>
                    <p class="why-choose-us__card-description">
                        Nessun costo nascosto. Solo tariffe chiare, fisse e competitive. Ottieni subito il preventivo definitivo.
                    </p>
                </div>

                <div class="why-choose-us__card">
                    <div class="why-choose-us__card-icon">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <path d="M12 6v6l4 2"></path>
                        </svg>
                    </div>
                    <h3 class="why-choose-us__card-title">Supporto 24/7</h3>
                    <p class="why-choose-us__card-description">
                        Team di supporto sempre disponibile per rispondere alle tue domande e risolvere qualsiasi problema.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Gallery Section -->
    <section class="gallery" id="gallery">
        <div class="gallery__container">
            <h2 class="gallery__title">Gallery</h2>
            <p class="gallery__subtitle">Scopri come curiamo ogni dettaglio del trasporto della tua moto.</p>
            
            <div class="gallery__grid">
                <div class="gallery__item">
                    <img src="https://via.placeholder.com/400x300/667eea/ffffff?text=Trasporto+Moto+1" alt="Trasporto moto 1" class="gallery__image">
                </div>
                <div class="gallery__item">
                    <img src="https://via.placeholder.com/400x300/764ba2/ffffff?text=Trasporto+Moto+2" alt="Trasporto moto 2" class="gallery__image">
                </div>
                <div class="gallery__item">
                    <img src="https://via.placeholder.com/400x300/667eea/ffffff?text=Trasporto+Moto+3" alt="Trasporto moto 3" class="gallery__image">
                </div>
                <div class="gallery__item">
                    <img src="https://via.placeholder.com/400x300/764ba2/ffffff?text=Trasporto+Moto+4" alt="Trasporto moto 4" class="gallery__image">
                </div>
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
                <a href="#contact" class="btn btn--dark btn--large">Preventivo Gratuito</a>
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

    <script src="js/main.js"></script>
</body>

</html>