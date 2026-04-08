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
            <div class="quote-stepper__line"></div>
            <div class="quote-stepper__step" data-step="6">
                <div class="quote-stepper__circle">
                    <svg class="quote-stepper__icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="1" y="4" width="22" height="16" rx="2"></rect>
                        <line x1="1" y1="10" x2="23" y2="10"></line>
                    </svg>
                    <svg class="quote-stepper__check" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                </div>
                <span class="quote-stepper__label">Pagamento</span>
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
                                <option value="0">No, non ci sono borse laterali - &euro;0</option>
                                <option value="30">S&igrave;, ma le faccio trovare smontate - &euro;30</option>
                                <option value="70">S&igrave;, ma non sono smontabili - &euro;70</option>
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

                    <!-- Riepilogo tratta -->
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
                                <strong class="quote-route-costs__value" id="routeDistance">&mdash;</strong>
                            </div>
                            <div class="quote-route-costs__item">
                                <span class="quote-route-costs__label">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10" />
                                        <polyline points="12 6 12 12 16 14" />
                                    </svg>
                                    Durata stimata
                                </span>
                                <strong class="quote-route-costs__value" id="routeDuration">&mdash;</strong>
                            </div>
                            <div class="quote-route-costs__divider"></div>
                            <div class="quote-route-costs__item">
                                <span class="quote-route-costs__label">Carburante (furgone benzina)</span>
                                <strong class="quote-route-costs__value" id="routeFuelCost">&mdash;</strong>
                            </div>
                            <div class="quote-route-costs__item">
                                <span class="quote-route-costs__label">Pedaggi autostradali</span>
                                <strong class="quote-route-costs__value" id="routeTollCost">&mdash;</strong>
                            </div>
                            <div class="quote-route-costs__divider"></div>
                            <div class="quote-route-costs__item quote-route-costs__item--total">
                                <span class="quote-route-costs__label">Costo trasporto</span>
                                <strong class="quote-route-costs__value" id="routeTotalCost">&mdash;</strong>
                            </div>
                            <p class="quote-route-costs__note" id="routeMinNote" style="display:none;">* Spesa minima di &euro;50 applicata</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- STEP 3: Data & Tipo di consegna -->
            <div class="quote-step quote-step--hidden" id="quoteStep3">
                <h3 class="quote-step__title">Data e tipo di ritiro</h3>
                <div class="quote-form">
                    <div class="quote-form__group">
                        <label class="quote-form__label" for="pickupDate">Data di ritiro <span class="quote-form__required">*</span></label>
                        <input class="quote-form__input" type="date" id="pickupDate" name="pickupDate"
                            min="<?= date('Y-m-d', strtotime('+1 day')) ?>">
                        <small class="quote-form__hint">Seleziona il giorno in cui vogliamo ritirare la moto</small>
                    </div>

                    <div class="quote-form__separator"></div>

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
                                <span class="quote-delivery-option__price">+50&euro;</span>
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
                                <span class="quote-delivery-option__price">+100&euro;</span>
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
                                <strong class="quote-summary__value" id="summaryMoto">&mdash;</strong>
                                <span class="quote-summary__desc" id="summaryMotoType">&mdash;</span>
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
                                <strong class="quote-summary__value" id="summaryRoute">&mdash;</strong>
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
                                <strong class="quote-summary__value" id="summaryDelivery">&mdash;</strong>
                                <span class="quote-summary__desc" id="summaryDeliveryDesc">&mdash;</span>
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
                                <span class="quote-summary__sublabel">Data di ritiro</span>
                                <strong class="quote-summary__value" id="summaryDate">&mdash;</strong>
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
                                <strong class="quote-summary__value" id="summaryName">&mdash;</strong>
                                <span class="quote-summary__desc" id="summaryContact">&mdash;</span>
                            </div>
                            <svg class="quote-summary__icon" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                        </div>
                    </div>

                    <div class="quote-summary__price-card">
                        <span class="quote-summary__price-label">Prezzo Totale</span>
                        <strong class="quote-summary__price-value" id="summaryPrice">&mdash;</strong>
                        <span class="quote-summary__price-note">IVA inclusa</span>
                    </div>

                    <p class="quote-summary__disclaimer">Cliccando "Conferma e Paga" verrai portato al pagamento sicuro integrato.</p>
                </div>

                <!-- Pannello successo salvataggio bozza (nascosto di default) -->
                <div class="quote-draft-saved" id="quoteDraftSaved" hidden>
                    <svg class="quote-draft-saved__icon" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                        <polyline points="17 21 17 13 7 13 7 21"></polyline>
                        <polyline points="7 3 7 8 15 8"></polyline>
                    </svg>
                    <h4 class="quote-draft-saved__title">Preventivo salvato!</h4>
                    <p class="quote-draft-saved__msg">Trovi la bozza nella tua dashboard e hai tempo di completare il pagamento entro il <strong id="quoteDraftExpiry">&mdash;</strong>.</p>
                    <a class="quote-draft-saved__link" href="/dashboard?sezione=ordini">Vai alla dashboard &rarr;</a>
                </div>
            </div>

            <!-- STEP 6: Pagamento Stripe -->
            <div class="quote-step quote-step--hidden" id="quoteStep6">
                <h3 class="quote-step__title">Pagamento sicuro</h3>

                <!-- Riepilogo importo nel pagamento -->
                <div class="quote-payment__summary">
                    <div class="quote-payment__summary-row">
                        <span>Trasporto moto</span>
                        <strong id="paymentSummaryMoto">—</strong>
                    </div>
                    <div class="quote-payment__summary-divider"></div>
                    <div class="quote-payment__summary-row quote-payment__summary-row--total">
                        <span>Totale da pagare</span>
                        <strong id="paymentSummaryTotal">—</strong>
                    </div>
                </div>

                <!-- Stripe Payment Element -->
                <div id="stripePaymentElement" class="quote-payment__element"></div>
                <div id="stripePaymentError" class="quote-payment__error" role="alert" style="display:none;"></div>

                <!-- Sicurezza badge -->
                <div class="quote-payment__security">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="11" width="18" height="11" rx="2"></rect>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                    <span>Pagamento crittografato con SSL. Dati gestiti da <strong>Stripe</strong>.</span>
                </div>
            </div>

            <!-- STEP 7: Successo -->
            <div class="quote-step quote-step--hidden" id="quoteStep7">
                <div class="quote-success">
                    <div class="quote-success__icon">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                        </svg>
                    </div>
                    <h3 class="quote-success__title">Pagamento confermato!</h3>
                    <p class="quote-success__text">Il tuo ordine di trasporto moto è stato confermato. Riceverai una email di conferma a <strong id="successEmail"></strong>.</p>
                    <div class="quote-success__details">
                        <div class="quote-success__detail">
                            <span class="quote-success__detail-label">N° preventivo</span>
                            <strong class="quote-success__detail-value" id="successPreventivoId">—</strong>
                        </div>
                        <div class="quote-success__detail">
                            <span class="quote-success__detail-label">Importo pagato</span>
                            <strong class="quote-success__detail-value" id="successImporto">—</strong>
                        </div>
                    </div>
                    <button class="quote-btn quote-btn--next" id="quoteSuccessClose" style="margin-top:1.5rem;">
                        Chiudi
                    </button>
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
            <?php if (!empty($user)): ?>
                <button class="quote-btn quote-btn--save-draft" id="quoteSaveDraftBtn" style="display:none;" title="Salva il preventivo per completare il pagamento in seguito">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                        <polyline points="17 21 17 13 7 13 7 21"></polyline>
                        <polyline points="7 3 7 8 15 8"></polyline>
                    </svg>
                    Salva per dopo
                </button>
            <?php endif; ?>
            <button class="quote-btn quote-btn--confirm" id="quoteConfirmBtn" style="display:none;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="1" y="4" width="22" height="16" rx="2"></rect>
                    <line x1="1" y1="10" x2="23" y2="10"></line>
                </svg>
                Vai al pagamento
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
            </button>
            <button class="quote-btn quote-btn--pay" id="quotePayBtn" style="display:none;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="11" width="18" height="11" rx="2"></rect>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
                Paga ora
            </button>
        </div>

    </div>
</div>