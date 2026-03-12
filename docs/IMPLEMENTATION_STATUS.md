# Reports IVF - Pocetni status implementacije

## Uradjeno u ovom koraku

1. Laravel 12 projekat je inicijalizovan u folderu `backend`.
2. Dodan je pocetni data model iz specifikacije (migracije + Eloquent modeli):
   - `locations`
   - `service_categories`, `services`
   - `finding_categories`, `findings`
   - `staff_members`
   - `daily_reports`, `daily_report_items`, `daily_report_finding_items`
   - `report_email_settings`, `report_configurations`
   - `audit_logs`
   - `payment_methods`
   - pivot tabele `location_staff` i `user_location`
   - prosiren `users` model (role, aktivnost, prava za podnosenje izvjestaja)
3. Breeze + Blade autentikacija je instalirana i aktivna:
   - login/register/reset/verification flow
   - navigacija i dashboard unutar auth layout-a
4. Dodan middleware sloj:
   - `active` middleware (blokira neaktivne korisnike)
   - `role` middleware (kontrola pristupa po ulozi)
5. Implementiran modul iz Faze 1: CRUD `Lokacije`:
   - lista sa filterima i pretragom
   - kreiranje, prikaz, izmjena
   - deaktivacija (umjesto tvrdog brisanja)
   - route zastita za `glavni_admin` i `administrator_klinike`
6. Implementiran modul iz Faze 1: CRUD `Usluge i kategorije`:
   - kategorije usluga (lista/filter/create/show/edit/deaktivacija)
   - usluge (lista/filter/create/show/edit/deaktivacija)
   - povezivanje usluge sa kategorijom
7. Implementiran modul iz Faze 1: CRUD `Nalazi i kategorije`:
   - kategorije nalaza (lista/filter/create/show/edit/deaktivacija)
   - nalazi (lista/filter/create/show/edit/deaktivacija)
   - povezivanje nalaza sa kategorijom i opcionalno sa uslugom
8. Dodani su odnosi i castovi na modelima za brzi nastavak CRUD implementacije.
9. Implementiran modul iz Faze 1: CRUD `Medicinski tim`:
   - lista/filter/create/show/edit/deaktivacija
   - dodjela clana tima na jednu ili vise lokacija
   - role-based zastita (admin i administrator klinike)
10. Implementiran glavni ekran `Dnevni izvjestaj`:
   - zaglavlje izvjestaja (datum, lokacija, status, napomena)
   - unos stavki usluga sa statusom placanja i automatskim obracunom duga
   - unos stavki nalaza sa obracunom ukupne vrijednosti
   - zbirne metrike (promet, naplaceno, dug, nalazi)
   - podnosenje izvjestaja i vracanje u rad (admin)
11. Implementiran modul `Korisnici i dozvole`:
   - lista/filter/create/show/edit/deaktivacija korisnika
   - upravljanje rolama (`glavni_admin`, `administrator_klinike`, `medicinska_sestra`)
   - dozvole (`can_submit_report`, `can_change_submitter`)
   - pristup lokacijama (many-to-many `user_location`)
12. Implementirana email automatizacija i scheduler:
   - queue job za dnevni izvjestaj nakon podnosenja (`SendDailyReportEmailJob`)
   - queue job za sedmicni/mjesecni zbirni izvjestaj (`SendPeriodicReportEmailJob`)
   - mailable template-i za dnevni i periodicne izvjestaje
   - Artisan komande:
     - `reports:send-weekly-summary`
     - `reports:send-monthly-summary`
   - scheduler:
     - sedmicni: nedjelja 20:00
     - mjesecni: prvi dan mjeseca 07:00
13. Seedovani su:
   - glavni admin korisnik: `admin@reports-ivf.local` / `admin12345`
   - default metode placanja: Fiskalno, Nefiskalno, Karticno, Ziralno
   - default email primaoci (daily/weekly/monthly): `admin@reports-ivf.local`
14. Migracije i testovi prolaze:
   - `php artisan migrate:fresh --seed`
   - `php artisan test`

## Sljedeci predlozeni koraci (Faza 1)

1. Napraviti CRUD ekrane:
   - (Faza 1 CRUD moduli su zavrseni)
2. Dodati podesavanja email primalaca kroz UI modul (opciono, trenutno podrzano kroz tabelu `report_email_settings`).
3. Dodati napredne analiticke prikaze i export (PDF/Excel) za sedmicne i mjesecne izvjestaje.
