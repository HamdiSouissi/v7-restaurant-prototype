# Verifica del prototipo

Verifica eseguita il **12 novembre 2026** con PHP 8.5, Chromium headless e Playwright. Il progetto non dipende dagli strumenti utilizzati per i test.

## Controlli superati

- Sintassi PHP (`php -l index.php`) e JavaScript (`node --check assets/js/app.js`).
- Caricamento della pagina, lingua italiana e risorse locali: nessuna risposta HTTP di errore e nessun errore JavaScript nei percorsi verificati.
- Sei categorie del menù e selezione tramite frecce, Home ed End.
- Aggiunta al carrello, incremento/decremento, eliminazione e stato vuoto.
- Calcoli: € 32 di piatti + € 4 di consegna = € 36; raggiunti € 50, consegna gratuita; ritiro senza spese.
- Ripristino del carrello dopo il ricaricamento; nessun nome, recapito o indirizzo nei dati salvati.
- Ordine per asporto e ordine per consegna con indirizzo obbligatorio, consenso, conferma e svuotamento del carrello.
- Prenotazione: errori dei campi obbligatori, numero di telefono non valido, lunedì non prenotabile, selezione degli ospiti e dell’ambiente, conferma e ripristino del modulo.
- FAQ: apertura, chiusura e un solo pannello aperto.
- Newsletter: email errata, email valida e messaggio dimostrativo.
- Apertura e chiusura di privacy, note legali e informazioni sui cookie.
- Galleria: carosello, foto a schermo intero, frecce della tastiera, zoom, Esc e ripristino del focus.
- Animazioni GSAP, scorrimento Lenis, hover, blocco dello sfondo durante i dialoghi e contesto con movimento ridotto.
- Navigazione mobile e carrello su schermo stretto.
- Nessuno scorrimento orizzontale del documento alle larghezze **320, 360, 390, 768, 1024, 1280, 1920 e 2560 px**.
- Carta completa e statistiche leggibili senza JavaScript; moduli non attivi in assenza dei gestori front-end.

## Accessibilità automatica

L’analisi axe-core delle regole WCAG A/AA non ha rilevato violazioni nelle superfici verificate: pagina principale, carrello e modulo d’ordine. Questo risultato non sostituisce una verifica manuale con tecnologie assistive e non rappresenta una certificazione di conformità.

Sono stati inoltre controllati visivamente la pagina su desktop, i singoli capitoli, il modulo di prenotazione, il footer e il carrello mobile.

## Da verificare per una pubblicazione reale

- Safari, Firefox, dispositivi iOS/Android reali e navigazione con lettore di schermo.
- Contenuti, recapiti, orari, allergeni, prezzi, fotografie e diritti d’uso della struttura effettiva.
- Privacy e consenso per eventuali servizi esterni, email o strumenti di analisi.
- Disponibilità reale dei tavoli, gestione degli ordini, protezioni server-side e provider di pagamento.
- Politiche stagionali e logistiche del ristorante reale.

Il prototipo non offre nessuno di questi servizi reali: tutte le conferme rimangono simulazioni sul dispositivo del visitatore.
