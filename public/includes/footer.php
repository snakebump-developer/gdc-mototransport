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
                        <span><?= htmlspecialchars(env('COMPANY_ADDRESS', 'Via Example 123, 20100 Milano (MI)'), ENT_QUOTES, 'UTF-8') ?></span>
                    </li>
                    <li>
                        <div class="footer__contact-icon">
                            <i class="fa-solid fa-phone"></i>
                        </div>
                        <span><?= htmlspecialchars(env('COMPANY_PHONE', '+39 012 345 6789'), ENT_QUOTES, 'UTF-8') ?></span>
                    </li>
                    <li>
                        <div class="footer__contact-icon">
                            <i class="fa-solid fa-envelope"></i>
                        </div>
                        <span><?= htmlspecialchars(env('COMPANY_EMAIL', 'info@mototransport.it'), ENT_QUOTES, 'UTF-8') ?></span>
                    </li>
                </ul>
            </div>
        </div>

        <div class="footer__bottom">
            <p class="footer__copyright">&copy; <?= date('Y') ?> <?= htmlspecialchars(env('COMPANY_NAME', 'MotoTransport Italia'), ENT_QUOTES, 'UTF-8') ?>. Tutti i diritti riservati.</p>
            <div class="footer__legal">
                <a href="#">Privacy Policy</a>
                <a href="#">Termini e Condizioni</a>
                <a href="#">Cookie Policy</a>
            </div>
        </div>
    </div>
</footer>