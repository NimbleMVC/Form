# <h1 align="center">NimblePHP - Form</h1>
Pakiet dostarcza kompleksowe narzędzia do tworzenia i walidacji formularzy w aplikacjach PHP. Zaprojektowany z myślą o 
prostocie, umożliwia łatwe generowanie dynamicznych formularzy oraz ich validowanie.

**Dokumentacja** projektu dostępna jest pod linkiem:
https://nimblemvc.github.io/documentation/extension/form/start/#

## Instalacja
```shell
composer require nimblephp/form
```

## Konfiguracja

### Zmienne środowiskowe

#### FORM_COPY_ASSET
- **Typ**: boolean
- **Domyślna wartość**: `true`
- **Opis**: Określa, czy biblioteka powinna automatycznie kopiować plik `form.js` do katalogu `public/assets/`. Gdy ustawiona na `true`, plik jest kopiowany podczas rejestracji pakietu. Można wyłączyć tę funkcję, ustawiając zmienną na `false`, jeśli chcesz ręcznie zarządzać zasobami.

**Przykład użycia w .env:**
```env
FORM_COPY_ASSET=false
```

## Użycie
TODO

## Współtworzenie
Zachęcamy do współtworzenia! Masz sugestie, znalazłeś błędy, chcesz pomóc w rozwoju? Otwórz issue lub prześlij pull request.

## Pomoc
Wszelkie problemy oraz pytania należy zadawać przez zakładkę discussions w github pod linkiem:
https://github.com/NimbleMVC/Form/discussions