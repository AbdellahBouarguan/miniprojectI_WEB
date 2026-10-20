(() => {
    const cfg = window.CONSO;

    if (!cfg) {
        return;
    }

    const form = document.getElementById('form-conso');
    const alerte = document.getElementById('anomaly_alert');
    const alerteTitre = document.getElementById('anomaly_title');
    const alerteTexte = document.getElementById('anomaly_text');
    const boutonSubmit = document.getElementById('submit_conso');

    const nombre = new Intl.NumberFormat('fr-FR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });

    const quantite = new Intl.NumberFormat('fr-FR', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    });

    const dh = (valeur) => `${nombre.format(valeur)} DH`;
    const m3 = (valeur) => `${quantite.format(valeur)} m³`;
    const kwh = (valeur) => `${quantite.format(valeur)} kWh`;

    const champs = [
        {
            code: 'eau',
            input: document.getElementById('index_eau'),
            error: document.getElementById('error_eau'),
            ok: document.getElementById('ok_eau'),
            unite: 'm³',
            formater: m3,
        },
        {
            code: 'elec',
            input: document.getElementById('index_elec'),
            error: document.getElementById('error_elec'),
            ok: document.getElementById('ok_elec'),
            unite: 'kWh',
            formater: kwh,
        },
    ];

    const calculerMontant = (consommation, tranches) => {
        let reste = Math.max(0, consommation);
        let total = 0;

        for (const tranche of tranches) {
            if (reste <= 0) {
                break;
            }

            const borneMax = tranche.borne_max === null
                ? Infinity
                : Number(tranche.borne_max);
            const largeur = Math.max(0, borneMax - Number(tranche.borne_min));
            const quantiteFacturee = Math.min(reste, largeur);

            total += quantiteFacturee * Number(tranche.prix);
            reste -= quantiteFacturee;
        }

        return total;
    };

    const lireIndex = (valeur) => {
        if (typeof valeur !== 'string' || valeur.trim() === '') {
            return null;
        }

        const normalisee = valeur.trim().replace(',', '.');

        if (!/^\d+(\.\d{1,2})?$/.test(normalisee)) {
            return NaN;
        }

        return Number(normalisee);
    };

    const afficherMessage = (element, message, visible) => {
        if (!element) {
            return;
        }

        element.textContent = message;
        element.hidden = !visible;
    };

    const mettreAJour = () => {
        let erreurIndex = false;
        let depassementSeuil = false;
        const resultats = {};

        for (const champ of champs) {
            if (!champ.input) {
                continue;
            }

            const precedent = cfg.precedent
                ? Number(cfg.precedent[champ.code])
                : 0;
            const seuil = Number(cfg.seuils[champ.code]);
            const index = lireIndex(champ.input.value);

            afficherMessage(champ.error, '', false);
            afficherMessage(champ.ok, '', false);

            if (index === null) {
                resultats[champ.code] = {conso: 0, montant: 0};
                continue;
            }

            if (Number.isNaN(index) || index < 0) {
                afficherMessage(
                    champ.error,
                    '⚠ Valeur numérique positive attendue.',
                    true
                );
                erreurIndex = true;
                resultats[champ.code] = {conso: 0, montant: 0};
                continue;
            }

            if (cfg.precedent && index < precedent) {
                afficherMessage(
                    champ.error,
                    `⚠ Index inférieur à l’index précédent (${nombre.format(precedent)}).`,
                    true
                );
                erreurIndex = true;
                resultats[champ.code] = {conso: 0, montant: 0};
                continue;
            }

            if (index > seuil) {
                depassementSeuil = true;
            }

            const consommation = Math.max(0, index - precedent);
            const montant = calculerMontant(
                consommation,
                cfg.tarifs[champ.code.toUpperCase()] || []
            );

            resultats[champ.code] = {
                conso: consommation,
                montant,
            };

            if (index > seuil) {
                afficherMessage(
                    champ.ok,
                    `Consommation calculée : +${champ.formater(consommation)} — seuil de vigilance dépassé.`,
                    true
                );
            } else {
                afficherMessage(
                    champ.ok,
                    `Consommation calculée : +${champ.formater(consommation)} — cohérent.`,
                    true
                );
            }
        }

        const eau = resultats.eau || {conso: 0, montant: 0};
        const elec = resultats.elec || {conso: 0, montant: 0};
        const totalHT = eau.montant + elec.montant;
        const tva = totalHT * (Number(cfg.tva) / 100);
        const totalTTC = totalHT + tva;

        const setTexte = (id, texte) => {
            const element = document.getElementById(id);

            if (element) {
                element.textContent = texte;
            }
        };

        setTexte('sim_eau_qty', m3(eau.conso));
        setTexte('sim_eau_total', dh(eau.montant));
        setTexte('sim_elec_qty', kwh(elec.conso));
        setTexte('sim_elec_total', dh(elec.montant));
        setTexte('sim_ht', dh(totalHT));
        setTexte('sim_tva', dh(tva));
        setTexte('sim_ttc', dh(totalTTC));

        if (alerte) {
            if (erreurIndex) {
                alerteTitre.textContent = 'Anomalie de saisie détectée';
                alerteTexte.textContent = 'Un index est inférieur à l’index précédent. La facture ne sera pas générée automatiquement. Corrigez les index avant de soumettre.';
                alerte.classList.add('show');
            } else if (depassementSeuil) {
                alerteTitre.textContent = 'Seuil de vigilance dépassé';
                alerteTexte.textContent = 'Votre consommation dépasse le seuil de +40 %. Vous pourrez confirmer votre relevé après l’envoi.';
                alerte.classList.add('show');
            } else {
                alerte.classList.remove('show');
            }
        }

        if (boutonSubmit) {
            boutonSubmit.disabled = erreurIndex || (!cfg.periodeOuverte && !cfg.correction);
        }
    };

    champs.forEach((champ) => {
        if (champ.input) {
            champ.input.addEventListener('input', mettreAJour);
        }
    });

    if (form) {
        form.addEventListener('reset', () => {
            window.setTimeout(() => {
                document.querySelectorAll('.photo-preview').forEach((image) => {
                    image.removeAttribute('src');
                    image.classList.remove('show');
                });

                mettreAJour();
            }, 0);
        });
    }

    mettreAJour();
})();
