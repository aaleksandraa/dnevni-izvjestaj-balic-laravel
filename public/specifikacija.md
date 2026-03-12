# Specifikacija aplikacije za dnevne, sedmične i mjesečne izvještaje klinike (IVF / opća klinika)

## 1. Uvod

Ovaj dokument predstavlja sveobuhvatnu funkcionalnu i tehničku specifikaciju aplikacije za vođenje dnevnih izvještaja u klinici, sa fokusom na IVF klinike, ali uz mogućnost korištenja i u drugim tipovima zdravstvenih ustanova. Cilj sistema je da omogući jednostavan unos dnevnih aktivnosti po lokaciji/ordinaciji, preciznu evidenciju usluga, nalaza, doktora, saradnika, radnika i načina plaćanja, kao i automatsko generisanje i slanje dnevnih, sedmičnih i mjesečnih izvještaja.

Sistem mora biti dovoljno jednostavan za svakodnevni rad medicinskog osoblja, ali istovremeno dovoljno detaljan da uprava i administracija u svakom trenutku mogu vidjeti kompletan promet, broj pregleda, dugovanja, realizaciju po doktorima i saradnicima, kao i sve istorijske podatke.

Aplikacija treba biti web bazirana, responzivna i prilagođena radu na desktopu, tabletima i iPhone/iPad uređajima, bez potrebe za posebnim nativnim iOS razvojem u prvoj fazi.

---

projekat koji će biti dnevni izvještaj iz IVF klinike, odnosno bilo koje klinike, ali u ovom slučaju IVF će biti u pitanju. Imamo sljedeće opcije. Možemo dodati ordinacije, jer recimo u nekoj klinici imamo više ordinacija u kojima se radi. Zatim možemo, to bi se možda zapravo moglo nazvati lokacije ordinacije. Podaci za lokacije bi trebali biti naziv lokacije, adresa, grad, telefon, meil i polje aktivna i neaktivna. Zatim trebamo imati spisak usluga, odnosno cjenovnik, gdje bi imali naziv usluge, može biti i kategorija i cijena i polje aktivna-neaktivna. Zatim spisak nalaza, to isto dolazi pod uslugama, ali možemo ga dodati kao posebnu kategoriju. Zatim imamo listu doktora. Dodavanje doktora, imamo polje ime i prezime, lokacija, na kojoj lokaciji radi doktor i da je moguće da radi i na više lokacija, a ulogu možemo dodati da bude doktor, da bude saradnik ili osoblje u smislu, možda medicinska sestra i tako dalje, ili neko drugi, a doktor su kao eto glavni, a saradnici su neko koji je spoljni faktori koji može da dolazi, sarađuje. Zatim imamo isto takođe polje aktivan i neaktivan. Radnici, to su, tu je spisak radnika, odnosno medicinskih sestara, koji će zapravo i popunjavati ove izvještaje. E sad ćemo dnevne izvještaje. Želim da na neki način bude da se može sačuvat', kad otvorimo, recimo, jednu ordinaciju i otvorimo današnji dan i možemo unositi podatke. Ali podatke želim da unosimo tako da unesemo ime i prezime osobe koja je bila na određenom pregledu, zatim izaberemo uslugu iz padajućeg menija koja je ona na kojoj je ona bila, zatim izaberemo doktora, zatim izaberemo plaćanje, odnosno ako popunjemo uslugu da je recimo neki pregled koji već ima cijenu od 50 KM, da se pod cijena pojavi odmah 50 KM, ali da mi možemo i ručno, ako želimo da izmjerimo tu cijenu na tom dnevnom izvještaju ukoliko je možda nešto naplaćeno više ili manje, nevažno, u tom trenutku. Zatim imamo polje da budu kolone, da bude kolona plaćeno ili neplaćeno. I ako izaberemo da je neplaćeno, da se otvori polje da se unese razlog, a ako izaberemo da je plaćeno, onda da se dodatna polja otvore, a to je fiskalno, nefiskalno, kartično ili žiralno. Pa da izaberemo koji je, na koji način je plaćeno i koliko, da automatski onda bude popunjeno sve, recimo 50, ali da postoji mogućnost da unesemo manje ukoliko je neko dao nešto manje, pa će možda na sljedećem pregledu dati više, nevažno, ali da pamtimo da sačuvamo da je ta osoba, recimo, platila cijeli iznos ili ostala dužna, kako bi to mogli evidentirati posle. I tako unosimo za sve ostale. Isto imamo dodatno za nalaze, to ćemo unositi pojedinačno u smislu samo će medicinska sestra koja podnosi taj izvještaj da doda nalaz i da doda koliko je bilo tih nalaza možda.Zatim, da kao glavni admin imamo podešavanja u kojima možemo da biramo šta će ići u dnevni izvještaj. Na primjer, ono što je bitno da možemo da izaberemo tačno ko su nam glavni doktori, a ko su zapravo primarni doktori, ko su sekundarni doktori, ko su saradnici. I da u dnevnom izvještaju možemo da izaberemo koji su nam podaci najvažniji kako bi se ti podaci slali i imejlom, a uopšten i potpuni izvještaj na kome će biti sve je zapravo default. Dakle, imamo koliko su primarni doktori danas zaradili, odnosno koliko su tačno imali broj pregleda, sveukupan broj pregleda, pa zatim po kategorijama, recimo, ime doktora, pa ispod ukupno ima 50 pregleda, pa ispod od tih 50 šta sadrži šta. Recimo, ginekološki pregled 3, ovaj pregled 5, ovaj 10, zatim da imamo sve ukupno, recimo, za saradnike bi to bilo na dnevnom izvještaju, recimo, svi saradnici su imali, ne znam, 50 pregleda od kojih je 30 ovih, 40 onih, 50 onih, i ukupne cijene koliko su zaradili glavni i primarni doktori, koliko saradnici i koji je današnji dnevni promet sveukupan.Takođe da možemo da podesimo na koje imejlove će stizati dnevni izvještaji. Pored dnevnih želimo na kraju i nedeljične izvještaje koji će stizati na kraju sedmice, u nedelju naveče ili, recimo, u ponedeljak ujutru za tu sedmicu. Odnosno za prethodnu sedmicu i to će biti samo sukupak sedmičnih, saberemo cijelu sedmicu i imaćemo i mjesečne izvještaje koji se šalje na kraju svakog mjeseca. Takođe na platformi želim da bude u svakom trenutku kad se neko uloguje da vidi sve podatke i dnevne izvještaje detaljno i da može da pristupi svemu i da pogleda sve. A da kada osoba unosi izvještaj današnji dnevni da uvijek može da edituje i da mijenja i sve to i da na kraju na dnu ima ima opciju podnesi današnji izvještaj i to je to. Kad klikne na to onda je izvještaj podnošen i šalje se avtomatskim imejlom na kao dnevni izvještaj osobama koje su podešene tako i...Da imamo različite sestre koje se uloguju sa različitih profila pa da automatski piše koja sestra je podijelila izvještaj, to je isto tako jako bitno da znamo tko je podnosilac izvještaja, ali na dnu izvještaja da može, da admin zapravo može da odredi da li sestra može podnositi samo sa svog profila svoj izvještaj ili može mijenjati podnosioca izvještaja i navesti neku drugu osobu.Sad kada detaljno pogledaš ovo, želim da napišeš jedan dokument, sveobuhvatan, detaljan, precizan, jasan, baš ovako kao što sam i ja pisala, sve opcije, sve, sve, sve u detalj, kako bi mogli napraviti dokument za napravimo ovakvu aplikaciju. Ono što želim da napomenem jeste da bi koristili MySQL kao bazu podataka. i ukoliko je tebe lakše da radimo sa Laravelom, možemo. Ili ako predlažeš neki drugi, ili nešto drugo, ali jako je bitno da napomenem da želim da bude i na iOS uređajima, tako da možda bez onih nouda i ostalog. Možda da radimo sa Laravel Bladeovima, ili tako nešto, jednostavno. i pisi na latinici

## 2. Glavni cilj sistema

Glavni cilj aplikacije je da klinika dobije centralizovan sistem u kojem:

* se unose dnevni pregledi i aktivnosti po lokaciji i datumu,
* se za svaku osobu evidentira koja je usluga izvršena,
* se evidentira koji je doktor obavio pregled,
* se evidentira status plaćanja i način plaćanja,
* se prati da li postoji dugovanje ili preostali iznos za naplatu,
* se vode dodatni nalazi i broj nalaza,
* se na kraju dana izvještaj može zaključiti i službeno podnijeti,
* se automatski šalju sažeti ili detaljni izvještaji na definisane email adrese,
* se kreiraju sedmični i mjesečni zbirni izvještaji,
* se zna koja je sestra/radnik unio i podnio izvještaj,
* admin može kontrolisati ko šta vidi, unosi, mijenja i podnosi.

---

## 3. Predloženi tehnološki pristup

## 3.1 Preporuka tehnologije

Za ovaj projekat preporuka je:

* **Backend:** Laravel
* **Frontend:** Laravel Blade
* **Baza podataka:** MySQL
* **Autentikacija:** Laravel built-in auth sa rolama i permisijama
* **Queue / email slanje:** Laravel queues + email jobs
* **Cron poslovi:** za sedmične i mjesečne izvještaje
* **UI:** responzivan admin panel prilagođen i mobilnim uređajima

## 3.2 Zašto Laravel + Blade

Ovaj pristup je najpraktičniji jer:

* omogućava brz razvoj poslovne aplikacije,
* stabilan je za kompleksne forme i administraciju,
* odlično radi sa MySQL bazom,
* može se napraviti kao klasična web aplikacija bez potrebe za Node-heavy frontendom,
* može biti maksimalno optimizovan za Safari/iPhone/iPad,
* lakše je održavanje nego kod odvojenog SPA + API + mobilnog app pristupa,
* za prvu verziju nema potrebe za posebnim iOS app-om ako je web aplikacija dobro prilagođena mobilnim uređajima.

## 3.3 iOS podrška

Pošto je traženo da sistem radi i na iOS uređajima, preporuka je da se aplikacija razvije kao:

* responzivna web aplikacija,
* sa posebno optimizovanim formama za mobilne uređaje,
* velikim klikabilnim elementima,
* jednostavnim unosom podataka,
* mogućnošću kasnije nadogradnje u PWA varijantu.

To znači da korisnici mogu koristiti aplikaciju kroz browser na iPhone/iPad uređajima bez komplikacija.

---

## 4. Korisničke uloge u sistemu

Sistem treba podržavati više korisničkih uloga.

### 4.1 Glavni admin

Glavni admin ima potpuni pristup sistemu, uključujući:

* upravljanje lokacijama,
* upravljanje uslugama i cjenovnikom,
* upravljanje listom nalaza,
* upravljanje doktorima, saradnicima i osobljem,
* upravljanje radnicima/sestrama,
* podešavanje izvještaja,
* podešavanje email primalaca izvještaja,
* pregled svih dnevnih, sedmičnih i mjesečnih izvještaja,
* izmjene svih izvještaja,
* zaključavanje/otključavanje izvještaja,
* definisanje ko može mijenjati podnosioca izvještaja,
* pregled istorije izmjena.

### 4.2 Administrator klinike / menadžer

Može imati ograničen pristup u odnosu na glavnog admina, npr:

* pregled svih izvještaja,
* pregled prometa,
* pregled podataka po lokacijama,
* pregled po doktorima,
* eventualno uređivanje izvještaja,
* bez pristupa sistemskim postavkama ako admin tako odluči.

### 4.3 Medicinska sestra / radnik

Ovo je operativni korisnik koji:

* otvara dnevni izvještaj za određeni datum i lokaciju,
* unosi pacijente/stavke/usluge,
* unosi nalaze,
* može sačuvati izvještaj u radu,
* može uređivati izvještaj dok nije finalno podnesen,
* na kraju klikom podnosi dnevni izvještaj,
* sistem evidentira ko je podnosilac.

### 4.4 Doktor / saradnik (opcionalno)

Ako bude potrebno, može imati read-only pristup ili ograničen uvid u vlastite izvještaje i rezultate.

---

## 5. Glavni moduli sistema

Sistem se sastoji od sljedećih modula:

1. Lokacije / ordinacije
2. Usluge / cjenovnik
3. Nalazi
4. Doktori i saradnici
5. Radnici / medicinske sestre
6. Dnevni izvještaji
7. Plaćanja i dugovanja
8. Podešavanja izvještaja
9. Email slanje izvještaja
10. Sedmični i mjesečni izvještaji
11. Pregled statistika i analitike
12. Istorija izmjena / audit log

---

## 6. Modul: Lokacije / ordinacije

Pošto jedna klinika može imati više ordinacija ili fizičkih mjesta rada, preporuka je da se ovaj modul nazove **Lokacije**. Po potrebi se u interfejsu može prikazivati kao “Lokacije / ordinacije”.

### 6.1 Polja za lokaciju

Za svaku lokaciju potrebno je imati:

* Naziv lokacije
* Adresa
* Grad
* Telefon
* Email
* Status: aktivna / neaktivna
* Opcionalno: napomena

### 6.2 Funkcionalnosti modula lokacija

* dodavanje nove lokacije,
* uređivanje postojeće lokacije,
* deaktivacija lokacije bez brisanja istorijskih podataka,
* filtriranje po statusu,
* povezivanje doktora sa jednom ili više lokacija,
* povezivanje dnevnih izvještaja sa tačno određenom lokacijom.

### 6.3 Poslovna logika

* neaktivna lokacija se više ne prikazuje za novi unos, ali ostaje u istoriji,
* jedan dnevni izvještaj mora biti vezan za jednu lokaciju,
* za isti datum treba omogućiti poseban izvještaj po svakoj lokaciji.

---

## 7. Modul: Usluge / cjenovnik

Ovaj modul predstavlja centralni katalog usluga koje klinika pruža.

### 7.1 Polja za uslugu

Za svaku uslugu potrebno je imati:

* Naziv usluge
* Kategorija usluge
* Osnovna cijena
* Status: aktivna / neaktivna
* Opcionalno: šifra usluge
* Opcionalno: opis
* Opcionalno: redoslijed prikaza

### 7.2 Primjeri kategorija

Na primjer:

* Ginekološki pregledi
* IVF konsultacije
* Kontrolni pregledi
* Ultrazvučni pregledi
* Proceduralne usluge
* Laboratorijske usluge
* Nalazi
* Ostalo

### 7.3 Poslovna logika za usluge

* kada korisnik u dnevnom izvještaju izabere uslugu, sistem automatski povlači njenu osnovnu cijenu,
* korisnik može ručno promijeniti cijenu na konkretnoj stavci izvještaja,
* promjena cijene u izvještaju ne smije mijenjati osnovni cjenovnik,
* neaktivne usluge ne smiju biti dostupne za nove unose, ali ostaju vezane za stare izvještaje.

---

## 8. Modul: Nalazi

Nalazi mogu biti izvedeni na dva načina:

### 8.1 Varijanta A – kao posebna kategorija unutar usluga

Ovo je jednostavniji pristup. Nalazi su samo usluge koje pripadaju kategoriji “Nalazi”.

### 8.2 Varijanta B – kao poseban modul

Ovo je fleksibilniji pristup. Preporuka za ovaj projekat je da postoji **poseban modul Nalazi**, ali da i dalje bude povezan sa uslugama/cjenovnikom.

### 8.3 Polja za nalaz

* Naziv nalaza
* Kategorija nalaza (opcionalno)
* Povezana usluga (ako postoji)
* Status: aktivan / neaktivan
* Opcionalno: jedinična cijena
* Opcionalno: napomena

### 8.4 Unos nalaza u dnevni izvještaj

Medicinska sestra treba moći:

* odabrati nalaz,
* unijeti količinu / broj komada,
* po potrebi dodati napomenu,
* sistem može izračunati ukupnu vrijednost ako nalaz ima definisanu cijenu.

---

## 9. Modul: Doktori, saradnici i osoblje

Ovaj modul treba objediniti sve stručne osobe koje učestvuju u radu klinike.

### 9.1 Polja za člana medicinskog tima

* Ime i prezime
* Uloga
* Jedna ili više lokacija na kojima radi
* Status: aktivan / neaktivan
* Opcionalno: titula
* Opcionalno: specijalizacija
* Opcionalno: email
* Opcionalno: telefon
* Opcionalno: interna šifra

### 9.2 Predložene uloge

* Primarni doktor
* Sekundarni doktor
* Saradnik
* Osoblje
* Ostalo

Napomena: u sistemu se uloga može voditi generički, a u podešavanjima izvještaja admin određuje koji tip korisnika ulazi u koju grupu obračuna.

### 9.3 Veza doktor – lokacija

Jedan doktor može raditi na više lokacija, tako da je potreban many-to-many odnos.

### 9.4 Poslovna logika

* u dnevnom izvještaju pri odabiru doktora treba prikazivati samo aktivne doktore,
* po želji se može dodatno filtrirati da se prikazuju samo doktori koji su povezani sa odabranom lokacijom,
* istorijski podaci moraju ostati vezani za doktora i kad postane neaktivan.

---

## 10. Modul: Radnici / medicinske sestre

Ovo su korisnici sistema koji svakodnevno popunjavaju izvještaje.

### 10.1 Polja za radnika

* Ime i prezime
* Email za prijavu
* Lozinka / autentikacioni podaci
* Status: aktivan / neaktivan
* Uloga korisnika
* Jedna ili više lokacija kojima ima pristup
* Dozvola za podnošenje izvještaja
* Dozvola za izmjenu podnosioca izvještaja
* Opcionalno: telefon

### 10.2 Bitna pravila

* sistem mora zapisivati ko je kreirao izvještaj,
* sistem mora zapisivati ko je posljednji mijenjao izvještaj,
* sistem mora zapisivati ko je zvanično podnio izvještaj,
* admin može definisati da li sestra može podnijeti izvještaj samo u svoje ime ili može odabrati drugo ime podnosioca.

---

## 11. Modul: Dnevni izvještaji

Ovo je glavni modul sistema.

### 11.1 Koncept rada

Korisnik otvara:

* određenu lokaciju,
* određeni datum (npr. današnji dan),
* i unosi sve dnevne stavke.

Izvještaj može postojati u više statusa:

* U radu
* Podnesen
* Zaključen
* Po potrebi: Vraćen na doradu

### 11.2 Zaglavlje dnevnog izvještaja

Na nivou jednog dnevnog izvještaja treba imati:

* Datum izvještaja
* Lokacija
* Podnosilac izvještaja
* Status izvještaja
* Vrijeme kreiranja
* Vrijeme posljednje izmjene
* Vrijeme podnošenja
* Napomena izvještaja

### 11.3 Stavke dnevnog izvještaja – pregledi/usluge

Za svaku pojedinačnu stavku pregleda potrebno je unijeti:

* Ime i prezime osobe / pacijenta
* Usluga (iz padajućeg menija)
* Doktor
* Automatski povučena cijena
* Ručna korekcija cijene po potrebi
* Status plaćanja: plaćeno / neplaćeno / djelimično plaćeno
* Način plaćanja ako je plaćeno
* Iznos plaćen
* Iznos duga / preostalo
* Razlog neplaćanja ako nije plaćeno
* Napomena
* Vrijeme unosa stavke
* Ko je unio stavku

### 11.4 Tok unosa jedne stavke

Primjer poslovne logike:

1. Sestra unese ime i prezime pacijenta.
2. Izabere uslugu iz padajućeg menija.
3. Sistem automatski popuni osnovnu cijenu usluge.
4. Sestra po potrebi izmijeni cijenu.
5. Izabere doktora koji je radio pregled.
6. Izabere status plaćanja.
7. Ako je neplaćeno, otvara se polje “Razlog neplaćanja”.
8. Ako je plaćeno ili djelimično plaćeno, otvaraju se polja za način plaćanja i iznos.
9. Sistem automatski izračuna ostatak duga ako plaćeni iznos nije jednak ukupnoj cijeni.
10. Stavka se sačuva unutar dnevnog izvještaja.

### 11.5 Status plaćanja

Preporuka je da sistem podržava tri statusa:

* Plaćeno
* Neplaćeno
* Djelimično plaćeno

Ovo je bolji pristup od samo “plaćeno / neplaćeno”, jer korisnik ima potrebu da evidentira parcijalnu uplatu.

### 11.6 Načini plaćanja

Ako je stavka plaćena ili djelimično plaćena, otvara se izbor načina plaćanja:

* Fiskalno
* Nefiskalno
* Kartično
* Žiralno

Po potrebi se može omogućiti i kombinovano plaćanje u kasnijoj fazi, ali za prvu verziju je prihvatljivo jedan dominantan način po stavci.

### 11.7 Dugovanje

Sistem mora pamtiti:

* ukupnu cijenu stavke,
* koliko je naplaćeno,
* koliko je ostalo duga,
* razlog neplaćanja,
* eventualnu napomenu.

Ovo je važno da se kasnije može pratiti da li je pacijent ostao dužan i koliko.

### 11.8 Poseban dio za nalaze

U okviru dnevnog izvještaja treba imati poseban segment “Nalazi”, gdje sestra može:

* odabrati nalaz,
* unijeti količinu,
* eventualno unijeti cijenu,
* po potrebi dodati napomenu.

### 11.9 Automatsko čuvanje i ručno čuvanje

Preporuka je da postoji:

* dugme **Sačuvaj**,
* opcionalni auto-save mehanizam,
* jasno označeno da je izvještaj još uvijek “u radu”.

Time se omogućava da se tokom dana izvještaj više puta dopunjava bez zaključavanja.

### 11.10 Podnošenje izvještaja

Na dnu dnevnog izvještaja treba postojati dugme:

**Podnesi današnji izvještaj**

Kada korisnik klikne:

* sistem provjerava validacije,
* sistem evidentira vrijeme podnošenja,
* sistem evidentira ko je podnio izvještaj,
* izvještaj prelazi u status “Podnesen”,
* automatski se pokreće slanje email izvještaja definisanim primaocima.

### 11.11 Uređivanje nakon podnošenja

Potrebno je definisati pravilo:

* običan korisnik možda više ne može mijenjati izvještaj nakon podnošenja,
* admin može otključati ili urediti izvještaj,
* sve izmjene nakon podnošenja moraju biti auditirane.

---

## 12. Podešavanja sadržaja izvještaja

Glavni admin treba imati poseban modul za kontrolu šta ulazi u izvještaje i kako se prikazuju zbirni podaci.

### 12.1 Grupisanje doktora

Admin treba moći odrediti:

* ko su primarni doktori,
* ko su sekundarni doktori,
* ko su saradnici,
* ko su ostali.

Ovo je bitno jer dnevni i zbirni izvještaji trebaju prikazivati promet i broj pregleda po ovim grupama.

### 12.2 Podesivi elementi izvještaja

Admin treba moći uključiti/isključiti da li će email sažetak sadržavati:

* ukupan broj pregleda,
* broj pregleda po doktorima,
* broj pregleda po kategorijama usluga,
* promet po doktorima,
* promet po grupama doktora,
* ukupan dnevni promet,
* broj neplaćenih stavki,
* broj djelimično plaćenih stavki,
* ukupan dug,
* pregled nalaza,
* podnosioca izvještaja,
* lokaciju,
* dodatne napomene.

### 12.3 Default potpuni izvještaj

Bez obzira na sažeti email, sistem treba uvijek imati **potpuni interni izvještaj** unutar platforme koji sadrži sve podatke.

---

## 13. Email izvještaji

### 13.1 Dnevni email izvještaj

Kada se izvještaj podnese, sistem automatski šalje email definisanim primaocima.

### 13.2 Podešavanja email primalaca

Admin treba moći definisati:

* jednu ili više email adresa za dnevne izvještaje,
* jednu ili više email adresa za sedmične izvještaje,
* jednu ili više email adresa za mjesečne izvještaje,
* da li su primaoci isti ili različiti po tipu izvještaja.

### 13.3 Sadržaj dnevnog email izvještaja

Preporučeni sadržaj:

* naziv klinike / lokacije,
* datum,
* podnosilac izvještaja,
* ukupan broj pregleda,
* ukupan promet,
* iznos naplaćenog,
* iznos neplaćenog / duga,
* pregled po primarnim doktorima,
* pregled po saradnicima,
* pregled po kategorijama usluga,
* broj i tip nalaza,
* eventualne napomene,
* link za pregled kompletnog izvještaja u sistemu.

---

## 14. Sedmični izvještaji

### 14.1 Vrijeme slanja

Sedmični izvještaj treba biti automatski generisan i poslan:

* u nedjelju naveče, ili
* u ponedjeljak ujutro,
* za prethodnu sedmicu.

Vrijeme slanja treba biti podesivo.

### 14.2 Sadržaj sedmičnog izvještaja

Sedmični izvještaj predstavlja zbir svih dnevnih izvještaja za odabranu sedmicu i sadrži:

* ukupan broj pregleda u sedmici,
* ukupan broj pregleda po lokacijama,
* ukupan broj pregleda po doktorima,
* promet po doktorima,
* promet po grupama doktora,
* ukupni promet sedmice,
* ukupan iznos naplaćen,
* ukupan iznos nenaplaćen / duga,
* pregled usluga po kategorijama,
* pregled nalaza,
* eventualne trendove po danima.

---

## 15. Mjesečni izvještaji

### 15.1 Vrijeme slanja

Mjesečni izvještaj se automatski šalje:

* na kraju mjeseca, ili
* prvog dana narednog mjeseca,
* za prethodni mjesec.

### 15.2 Sadržaj mjesečnog izvještaja

Mjesečni izvještaj sadrži:

* ukupan broj pregleda u mjesecu,
* ukupan promet u mjesecu,
* pregled po lokacijama,
* pregled po doktorima,
* pregled po grupama doktora,
* ukupan broj nalaza,
* ukupan dug i status naplate,
* pregled najčešćih usluga,
* poređenje po sedmicama ili danima.

---

## 16. Pregled podataka unutar platforme

Kada se korisnik prijavi u sistem, treba moći pristupiti podacima prema svojim ovlaštenjima.

### 16.1 Dashboard

Preporučeni početni ekran:

* današnji broj pregleda,
* današnji promet,
* broj neplaćenih stavki,
* broj djelimično plaćenih stavki,
* zadnji podneseni izvještaji,
* pregled po lokacijama,
* prečica za otvaranje današnjeg izvještaja.

### 16.2 Pregled svih izvještaja

Treba omogućiti filtere po:

* datumu,
* rasponu datuma,
* lokaciji,
* doktoru,
* podnosiocu,
* statusu izvještaja,
* statusu plaćanja,
* tipu usluge,
* kategoriji usluge.

### 16.3 Detaljni pregled izvještaja

Klikom na izvještaj korisnik vidi:

* sve unesene stavke,
* sva plaćanja,
* sve nalaze,
* zbirne iznose,
* historiju izmjena,
* ko je podnio izvještaj.

---

## 17. Pravila izmjena i audit log

Pošto se radi o osjetljivim poslovnim i medicinsko-administrativnim podacima, potrebno je voditi audit log.

### 17.1 Šta se loguje

* ko je kreirao izvještaj,
* ko je izmijenio izvještaj,
* šta je izmijenjeno,
* kada je izmijenjeno,
* ko je podnio izvještaj,
* ko je otključao ili vratio izvještaj na doradu,
* promjene cijena,
* promjene statusa plaćanja,
* promjene podnosioca izvještaja.

### 17.2 Zašto je važno

Ovo štiti kliniku od zabuna, grešaka i sporova, te osigurava punu kontrolu nad radom korisnika.

---

## 18. Validacije i poslovna pravila

### 18.1 Osnovne validacije

* lokacija je obavezna,
* datum izvještaja je obavezan,
* ime i prezime pacijenta je obavezno za stavku pregleda,
* usluga je obavezna,
* doktor je obavezan ako je ta stavka pregled/usluga vezana za doktora,
* cijena mora biti broj >= 0,
* plaćeni iznos mora biti broj >= 0,
* plaćeni iznos ne bi trebao biti veći od ukupnog iznosa bez posebne dozvole,
* razlog neplaćanja je obavezan kada je status “neplaćeno”,
* način plaćanja je obavezan kada je status “plaćeno” ili “djelimično plaćeno”.

### 18.2 Dodatna pravila

* za isti datum i lokaciju treba spriječiti dupliranje glavnog dnevnog izvještaja, osim ako admin dozvoli više smjena,
* neaktivni korisnici ne mogu pristupiti sistemu,
* neaktivne usluge/doktori/lokacije se ne nude u novim unosima,
* podneseni izvještaj se ne smije tiho mijenjati bez traga u sistemu.

---

## 19. Predložena struktura baze podataka (MySQL)

U nastavku je funkcionalni prijedlog glavnih tabela.

### 19.1 users

Za prijavu korisnika sistema.

Polja:

* id
* name
* email
* password
* role
* is_active
* can_submit_report
* can_change_submitter
* created_at
* updated_at

### 19.2 locations

Polja:

* id
* name
* address
* city
* phone
* email
* is_active
* notes
* created_at
* updated_at

### 19.3 services

Polja:

* id
* category_id
* name
* base_price
* is_active
* code
* description
* sort_order
* created_at
* updated_at

### 19.4 service_categories

Polja:

* id
* name
* is_active
* created_at
* updated_at

### 19.5 findings

Polja:

* id
* category_id
* service_id (nullable)
* name
* unit_price
* is_active
* notes
* created_at
* updated_at

### 19.6 finding_categories

Polja:

* id
* name
* is_active
* created_at
* updated_at

### 19.7 staff_members

Ova tabela može predstavljati doktore, saradnike i ostalo medicinsko osoblje.

Polja:

* id
* full_name
* role_type
* title
* specialty
* email
* phone
* is_active
* created_at
* updated_at

### 19.8 location_staff

Pivot tabela za vezu više-na-više između lokacija i doktora/osoblja.

Polja:

* id
* location_id
* staff_member_id

### 19.9 daily_reports

Polja:

* id
* report_date
* location_id
* created_by_user_id
* submitted_by_user_id
* status
* notes
* submitted_at
* last_edited_by_user_id
* created_at
* updated_at

### 19.10 daily_report_items

Glavne stavke pregleda/usluga.

Polja:

* id
* daily_report_id
* patient_full_name
* service_id
* doctor_id
* item_price
* payment_status
* payment_method
* paid_amount
* remaining_amount
* unpaid_reason
* notes
* entered_by_user_id
* created_at
* updated_at

### 19.11 daily_report_finding_items

Polja:

* id
* daily_report_id
* finding_id
* quantity
* unit_price
* total_price
* notes
* entered_by_user_id
* created_at
* updated_at

### 19.12 report_email_settings

Polja:

* id
* report_type
* email
* is_active
* created_at
* updated_at

### 19.13 report_configuration

Polja:

* id
* config_key
* config_value
* created_at
* updated_at

### 19.14 audit_logs

Polja:

* id
* user_id
* entity_type
* entity_id
* action
* old_values
* new_values
* description
* created_at

### 19.15 payment_methods

n
Opcionalna lookup tabela ako se želi fleksibilniji sistem.

Polja:

* id
* name
* is_active

---

## 20. Izvještajne metrike koje sistem mora računati

Sistem treba automatski računati najmanje sljedeće metrike.

### 20.1 Na nivou dana

* ukupan broj pregleda,
* broj pregleda po doktoru,
* broj pregleda po grupi doktora,
* broj pregleda po kategoriji usluge,
* ukupan promet,
* naplaćeni promet,
* nenaplaćeni iznos,
* djelimično naplaćeni iznos,
* ukupan preostali dug,
* broj nalaza,
* vrijednost nalaza,
* promet po lokaciji.

### 20.2 Na nivou sedmice i mjeseca

Iste metrike, ali agregirane za širi period.

---

## 21. Korisnički interfejs – preporučena struktura ekrana

### 21.1 Login ekran

Jednostavna prijava sa emailom i lozinkom.

### 21.2 Dashboard

Sažetak današnjih i recentnih podataka.

### 21.3 Lokacije

Lista + forma za dodavanje/uređivanje.

### 21.4 Usluge

Lista + kategorije + cijene.

### 21.5 Nalazi

Lista nalaza + količine + cijene.

### 21.6 Medicinski tim

Doktori, saradnici, osoblje.

### 21.7 Korisnici sistema

Radnici/sestre/admini.

### 21.8 Dnevni izvještaj – forma

Ekran treba imati:

* zaglavlje izvještaja,
* sekciju za unos pregleda/usluga,
* sekciju za unos nalaza,
* tabelarni pregled unesenih stavki,
* zbirne iznose na dnu,
* dugme Sačuvaj,
* dugme Podnesi današnji izvještaj.

### 21.9 Pregled arhive izvještaja

Tabela sa filterima i detaljnim pregledom.

### 21.10 Podešavanja

* konfiguracija grupa doktora,
* email primaoci,
* pravila podnosioca,
* izgled i sadržaj email izvještaja.

---

## 22. Posebne preporuke za UX

Pošto će sistem koristiti medicinsko osoblje u svakodnevnom radu, UX mora biti maksimalno praktičan.

### 22.1 Preporuke

* veliki inputi i dropdown meniji,
* brzo dodavanje više stavki zaredom,
* automatsko fokusiranje na sljedeće polje,
* pretraga usluga i doktora unutar dropdown-a,
* mogućnost dupliranja slične prethodne stavke,
* jasna boja/status za plaćeno, neplaćeno i djelimično plaćeno,
* sticky zbir na dnu ili sa strane,
* mobilno prilagođen prikaz.

---

## 23. Sigurnost i pristup podacima

### 23.1 Potrebno osigurati

* prijavu korisnika,
* role/permissions sistem,
* zaštitu pristupa po ovlaštenjima,
* audit log,
* sigurnu pohranu lozinki,
* zaštitu od slučajnog brisanja,
* soft delete gdje je smisleno,
* backup baze podataka.

### 23.2 Dodatno preporučeno

* dvostepena provjera za admin korisnike u kasnijoj fazi,
* IP i session monitoring,
* automatski logout nakon neaktivnosti.

---

## 24. Ne-funkcionalni zahtjevi

Sistem treba biti:

* brz i jednostavan za korištenje,
* stabilan za svakodnevni rad,
* optimizovan za desktop i mobilne uređaje,
* prilagođen iOS browserima,
* proširiv za više klinika ili više odjela u budućnosti,
* spreman za kasniju nadogradnju.

---

## 25. Preporuka za faznu izradu

### Faza 1 – Osnovni sistem

* autentikacija korisnika,
* lokacije,
* usluge i cjenovnik,
* doktori/saradnici,
* korisnici/radnici,
* dnevni izvještaji,
* osnovna plaćanja,
* podnošenje izvještaja,
* email dnevni izvještaj.

### Faza 2 – Napredni izvještaji

* sedmični i mjesečni izvještaji,
* napredna statistika,
* detaljni filteri,
* pregled duga i naplate.

### Faza 3 – Napredne dorade

* audit log detaljno,
* zaključavanje i workflow odobravanja,
* PDF export,
* Excel export,
* PWA mogućnosti,
* napredne notifikacije.

---

## 26. Ključne odluke i preporuke

Na osnovu svih zahtjeva, preporučuje se sljedeće:

1. Sistem raditi u **Laravel + Blade + MySQL** arhitekturi.
2. “Ordinacije” modelovati kao **Lokacije**.
3. Usluge i cjenovnik voditi centralno, sa mogućnošću ručne izmjene cijene u izvještaju.
4. Podržati status plaćanja: **plaćeno / neplaćeno / djelimično plaćeno**.
5. Pamtiti dugovanja po svakoj stavci.
6. Nalaze voditi u posebnom segmentu, ali povezano sa uslugama.
7. Omogućiti rad na izvještaju tokom dana uz status **u radu**.
8. Na kraju dana omogućiti formalno **podnošenje izvještaja**.
9. Automatski slati dnevne, sedmične i mjesečne email izvještaje.
10. Voditi evidenciju o tome ko je kreirao, uređivao i podnio izvještaj.
11. Omogućiti adminu da određuje pravila za podnosioca i sadržaj izvještaja.
12. Od početka projektovati sistem tako da radi responzivno i na iOS uređajima.

---

## 27. Zaključak

Predloženi sistem nije samo forma za unos dnevnih pregleda, nego kompletna poslovno-operativna aplikacija za kliniku. Njegova vrijednost je u tome što objedinjuje unos, evidenciju, finansijsko praćenje, kontrolu rada osoblja i automatsko izvještavanje upravi.

Za IVF kliniku ovakav sistem je posebno koristan jer omogućava jasnu dnevnu evidenciju rada po lokacijama, doktorima, uslugama i nalazima, uz precizno praćenje naplate i duga. Ujedno, kroz sedmične i mjesečne izvještaje uprava dobija kvalitetan pregled performansi klinike bez ručnog sabiranja i naknadne administracije.

Ako se sistem pravilno postavi od početka, može kasnije biti proširen i na dodatne module, druge tipove klinika, više poslovnica, detaljniju analitiku i druge interne procese.

---

## 28. Kratki sažetak za odluku o razvoju

Ovo rješenje treba razvijati kao:

* web aplikaciju,
* Laravel backend,
* Blade frontend,
* MySQL bazu,
* responzivni interfejs,
* email automatizaciju,
* role/permissions sistem,
* centralizovan modul za dnevne, sedmične i mjesečne izvještaje.

To je trenutno najpraktičniji, najstabilniji i najjednostavniji put da dobijete profesionalnu aplikaciju koja će raditi i na računarima i na iPhone/iPad uređajima.
