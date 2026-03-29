# Nota

A clean, minimalist WordPress theme for personal academic publishing.

Nota turns WordPress into a simple scholarly writing platform with margin sidenotes, multi-format citations (APA, MLA, Chicago, BibTeX), PDF and EPUB export, dark mode, reading mode, and optional project pages with team management and HAL publications integration.

## Origins

Nota was originally built as a custom theme for [Politics of Language](https://politicsoflanguage.org/), a linguistics and anthropology research project. 

The theme's design philosophy is inspired by the typographic traditions of academic publishing: serif body text, generous margins, marginal annotations (sidenotes), and a distraction-free reading experience.

---

## Features

### Writing & Reading
- **Sidenotes** -- WordPress footnotes (WP 6.3+) are automatically converted to Tufte-style margin notes on desktop and tap-to-reveal popovers on mobile
- **Reading mode** -- A distraction-free view that hides navigation and focuses on the article
- **Dark mode** -- Toggle between light and dark themes; respects system preference and persists via localStorage

### Academic Tools
- **Multi-format citations** -- Generate citations for any article in APA 7th, MLA 9th, Chicago 17th, and BibTeX (BibLaTeX `@online`) formats
- **Selection citations** -- Select any passage and cite it with a paragraph locator
- **PDF and EPUB export** -- Download any post as a valid PDF or EPUB  file.
- **References sidebar** -- Display bibliographic references alongside articles (via ACF fields)

### Project Pages
- **Project showcase template** -- A structured page for research projects with:
  - Key information (PI, period, funding, institution)
  - Team member profiles with links
  - Research themes/topics
  - HAL publications integration (live from the HAL open-access repository)
  - Manual publications management
- **Native meta boxes** -- Team, themes, links, and publications are managed via WordPress-native meta boxes (no ACF Pro required)

### Design & UX
- **Responsive** -- Mobile-first design with hamburger menu, touch-friendly popovers
- **Bilingual UI** -- Built-in English/French UI translations with Polylang integration
- **Header search** -- Animated search overlay in the header
- **CSS custom properties** -- All colors and fonts use CSS variables for easy theming
- **Print-friendly** -- Optimized print styles with references moved after body text
- **Accessible** -- ARIA labels, semantic HTML5, focus management

---

## Installation

### Manual Installation
1. Download or clone this repository
2. Copy the `nota` folder into `wp-content/themes/`
3. Go to **Appearance > Themes** in your WordPress admin
4. Activate **Nota**

### From ZIP
1. Download the theme as a ZIP file
2. Go to **Appearance > Themes > Add New > Upload Theme**
3. Upload the ZIP and activate

---

## Configuration

### WordPress Customizer

Go to **Appearance > Customize > Nota Theme** to configure:

#### General
| Setting | Description | Default |
|---------|-------------|---------|
| Footer text | Additional text after the copyright line | *(empty)* |
| Copyright format | Use `{year}` and `{site}` as placeholders | `© {year} {site}` |

#### Theme Colors
| Setting | Description | Default |
|---------|-------------|---------|
| Accent color | Links, highlights, branding | `#163316` (dark green) |
| Background color | Page background | `#fcfbf9` (cream) |
| Text color | Body text | `#0f140f` (near-black) |
| Background pattern | Toggle the dotted background texture | On |

#### Typography
| Setting | Description | Default |
|---------|-------------|---------|
| Body font (serif) | Font for body text | Libre Caslon Text |
| Heading font (sans) | Font for headings and UI | Cabin |

Available serif fonts: Libre Caslon Text, Georgia, Lora, Source Serif 4, Merriweather, EB Garamond

Available sans fonts: Cabin, Inter, Source Sans 3, Work Sans, Nunito Sans, System UI

#### Features
All features can be independently toggled on/off:
- Citation tools
- EPUB export
- Reading mode
- Sidenotes
- Header search
- Dark mode toggle
- Language switcher (requires Polylang)

---

## Project Page Template

The theme includes a **Project Page** template for showcasing research projects.

### Setup
1. Create a new Page in WordPress
2. In the Page Attributes panel, select **Project Page** as the template
3. Fill in the project information fields (requires ACF plugin, free version)

### ACF Fields
These fields appear when the Project Page template is selected:
- **Tagline** -- Short project description
- **Principal Investigator** -- Lead researcher name
- **Period** -- e.g., "2024--2029"
- **Funding body** -- Grant or funding organization
- **Institution** -- Host institution

### Native Meta Boxes
These are managed without ACF Pro:
- **Project Links** -- URL + label pairs
- **Team Members** -- Name, role, institution, bio, and personal links
- **Research Themes** -- Title + description
- **Manual Publications** -- Citation text, year, type, DOI, URL

### HAL Publications
To display publications from the [HAL open-access repository](https://hal.science):
1. Install ACF (free version)
2. On the Project Page, fill in either:
   - **HAL Author ID** (e.g., `james-costa`) -- fetches all publications by this author
   - **HAL Collection Code** -- fetches publications from a specific HAL collection
3. Publications are cached for 12 hours. Append `?flush_hal` to the URL to refresh.

---

## Adding Languages

The theme uses a lightweight translation system for UI strings. To add a new language:

1. Open `functions.php` and find the `nota_translations()` function
2. Add a new top-level key matching the Polylang language slug:

```php
function nota_translations() {
    return array(
        'fr' => array( /* existing French translations */ ),
        'de' => array(
            'Author'        => 'Autor',
            'Published on'  => 'Veröffentlicht am',
            'Read more'     => 'Weiterlesen →',
            // ... add all strings you want translated
        ),
    );
}
```

English is the source language (used as keys). Any untranslated string falls back to English automatically.

The UI language is determined by (in priority order):
1. `?ui_lang=` URL parameter
2. Polylang's current language
3. `nota_ui_lang` cookie
4. English (default)

---

## Developer Notes

### Function Reference

| Function | Description |
|----------|-------------|
| `nota_t( $string )` | Returns the translated UI string |
| `nota_e( $string )` | Echoes the translated UI string |
| `nota_ui_lang()` | Returns the current UI language slug |
| `nota_feature( $key )` | Checks if a feature is enabled (e.g., `nota_feature('citations')`) |
| `nota_post_has_notes( $post_id )` | Returns true if the post has footnotes/sidenotes |
| `nota_hal_publications( $author, $collection )` | Fetches publications from HAL API |
| `nota_hal_entry_html( $doc, $type )` | Renders a single HAL publication entry |
| `nota_google_fonts_url()` | Returns the Google Fonts URL based on Customizer settings |

### CSS Custom Properties

```css
:root {
    --bg-color: #fcfbf9;
    --text-color: #0f140f;
    --accent: #163316;
    --line-color: #e0e0e0;
    --font-serif: 'Libre Caslon Text', Georgia, serif;
    --font-sans: 'Cabin', sans-serif;
}
```

These are overridden by the Customizer settings. You can also override them in a child theme.

### Template Hierarchy

| Template | Purpose |
|----------|---------|
| `index.php` | Blog/post grid |
| `single.php` | Single article with sidebar, citations, sidenotes |
| `page.php` | Static pages |
| `page-project.php` | Project showcase (team, themes, publications) |
| `archive.php` | Archives (categories, tags, dates, authors) |
| `category.php` | Category archive with smart subcategory layout |
| `search.php` | Search results |
| `404.php` | Error page |

### Hooks & Filters
- The sidenote conversion hooks into `the_content` at priority 15
- The EPUB export hooks into `template_redirect`
- Custom CSS is output via `wp_head` at priority 20
- All features respect the Customizer toggles

---

## Requirements

- **WordPress 6.3+** (required for native footnotes/sidenotes)
- **PHP 7.4+**

### Optional Plugins
- **[Advanced Custom Fields](https://www.advancedcustomfields.com/)** (free) -- Required for Project Page fields (tagline, PI, period, funding, institution, HAL config)
- **[Polylang](https://polylang.pro/)** (free) -- For multilingual content and the language switcher

---

## License

Nota is released under the [GNU General Public License v3](https://www.gnu.org/licenses/gpl-3.0.html).

---

## Credits

- Originally built for [Politics of Language](https://politicsoflanguage.org/)
- Typography: [Libre Caslon Text](https://fonts.google.com/specimen/Libre+Caslon+Text) and [Cabin](https://fonts.google.com/specimen/Cabin) via Google Fonts
- Sidenote design inspired by [Edward Tufte's](https://www.edwardtufte.com/tufte/) typographic principles
- HAL integration via the [HAL open-access repository API](https://api.archives-ouvertes.fr/)
