# John Kim Harvey Dangate — Portfolio

A hand-written, two-tone PHP portfolio. Four pages, four playground tools, no
frameworks, no build step, no database.

```
portfolio/
├── index.php                  Home — intro, ID card, marquee, featured project
├── about.php                  Bio, skills, tools, currently learning
├── projects.php               JavaLearn Hub case study (edit $projects to add more)
├── playground.php             Four small tools
├── includes/
│   ├── header.php             <head>, nav array, logo  ← site name + nav live here
│   └── footer.php             Contact + footer
└── assets/
    ├── css/style.css          All styles (sections 1–15) + responsive media queries
    ├── js/main.js             All interactivity (6 modules, zero dependencies)
    └── img/
        ├── photo.jpg          ← REPLACE with your portrait (800 × 1000)
        ├── project-javalearnhub.jpg  ← REPLACE with a screenshot (1200 × 750)
        └── README.md          Photo instructions
```

---

## How to run it

A `.php` file needs PHP to process it — opening `index.php` by double-click shows
raw code, not a website. Pick either option:

### Option A — XAMPP (easiest on Windows)

1. Install [XAMPP](https://www.apachefriends.org/) and open the XAMPP Control Panel.
2. Click **Start** next to **Apache**.
3. Copy the whole `portfolio` folder into `C:\xampp\htdocs\`
   (so you have `C:\xampp\htdocs\portfolio\index.php`).
4. Open **http://localhost/portfolio/** in your browser.

### Option B — PHP's built-in server (any OS)

```bash
cd portfolio
php -S localhost:8000
```

Then open **http://localhost:8000**.

---

## What to change before submitting

| What | Where |
|---|---|
| Your email | `includes/footer.php` line ~34 **and** `index.php` line ~172 (`mailto:` + visible text) |
| Your GitHub profile | `includes/footer.php` line ~35 |
| JavaLearn Hub repo link | `projects.php` line ~49 |
| Launch year of the project | `projects.php` line ~20 |
| Your photo | `assets/img/photo.jpg` — same filename, just overwrite |
| Project screenshot | `assets/img/project-javalearnhub.jpg` — same filename |
| Skill names / levels | `about.php` → `$skills` array (`'level'` is 0–100) |
| Add a second project | `projects.php` → append another array to `$projects` — the page renders it automatically |

---

## The playground tools (`playground.php`)

| Tool | How it works | Needs |
|---|---|---|
| **01 Domain Age Check** | RDAP over HTTPS first, then WHOIS on port 43 as fallback. Returns registration date, age, expiry, registrar. | Outbound internet. The `curl` PHP extension makes RDAP reliable. |
| **02 HTTP Status Lookup** | A real cURL request (`NOBODY`); reports status code, redirects, DNS/TLS/total timing and flags broken certificates. | `curl` extension + internet. |
| **03 Password Strength** | Your browser hashes the password with SHA-256 (Web Crypto) and posts **only the hash and length**. PHP estimates entropy in bits. | Nothing — never sent in plain text. |
| **04 Text Case Lab** | Pure PHP string functions: ten case styles, word/character/sentence counts, reading time. | Nothing. |

All four work **with JavaScript disabled** too — the forms post normally and the
result is rendered server-side.

---

## Design rules this project follows

- **Two colours only** — `--ink #0c0c0c` and `--paper #f2efe6`, plus alpha
  variants. The dark/light toggle simply swaps which one is the background.
- **Mobile-first** — single column by default, extra columns added at
  640 / 760 / 900 / 1080 px. Nothing breaks at 320 px.
- **No frameworks, no libraries** — plain PHP includes, one CSS file, one JS file.
- **One source of truth for the nav** — `$nav` in `includes/header.php` drives
  every page; the active link comes from `$activePage`.

---

## Requirements

- PHP **8.0+** (built and tested on 8.3)
- The `curl` extension (standard in XAMPP) for playground tools 01 and 02
- A browser — that's it. No Composer, no npm, no database.

## Troubleshooting

**"It downloads the file instead of showing the page."**
You're opening it without PHP. Use Option A or B above.

**Tool 01 says it can't find a record.**
`.io` domains have no public RDAP record; the script falls back to WHOIS on port
43, which some school or office networks block. Try `.com` / `.site` domains.

**Tool 02 reports a certificate warning.**
That's the tool doing its job — the site's HTTPS certificate genuinely isn't
valid for that address (very common right after setting up a custom domain on
GitHub Pages).
