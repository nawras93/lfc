# Content API — handover sheet for the website team

**Purpose.** The Lusail SC fan app has no content of its own. Its Home page (news), Matches page (fixtures, results, league table) and news detail page all read live from an API. The website will hold this same content, so **the website is where it should live, and the website exposes it through the API described here.** The app then reads from you, and content is entered once.

**Direction of the dependency.** You build these endpoints as part of the site. We consume them. Nothing needs to be built on our side.

**This is not a wishlist — it is a working contract.** Every endpoint and payload below is already implemented and running in our demo backend, and the app is talking to it today. Match this shape and the app connects with no client changes. If you need to deviate, tell us *before* you build, not after.

---

## 1. Ground rules

| Rule | Detail |
|---|---|
| Auth | **None.** All of these are public, readable by any visitor. Do not put them behind a login. |
| Format | JSON, UTF-8. |
| Envelope | Every response is wrapped: `{"data": ...}`. Lists return `{"data": [...]}`, single items `{"data": {...}}`. |
| Empty state | An empty list is `{"data": []}` with **HTTP 200** — never a 404, never `null`. |
| Missing item | A news article that doesn't exist, or isn't published, is **HTTP 404**. |
| CORS | Must allow the app's origin. |
| Base path | Yours to choose (e.g. `https://lusailsc.qa/api/v1`). Give us the base URL; the paths below hang off it. |

## 2. Language

**One request, one language.** The client sends a standard header:

```
Accept-Language: ar        (or: en)
```

You return the strings **already translated, in the same JSON fields**. Do **not** return `title` and `title_ar` side by side and expect the client to choose — the client sends the header and renders whatever comes back.

If the header is missing, default to English. If a given item has no Arabic translation, fall back to the English text rather than returning an empty string.

Fields that are translated: `title`, `excerpt`, `body`, `opponent`, `competition`, `club_name`.

## 3. Endpoints

### `GET /content/news` — news list (Home page)

```json
{
  "data": [
    {
      "id": 3,
      "title": "Club president honoured with the Sports & Youth Excellence Award",
      "excerpt": "Lusail SC president Nawaf Mohammed Al-Mudahka received the Sports and Youth Excellence Award on behalf of the Sumaismah Youth Centre.",
      "image_url": "https://lusailsc.qa/media/news/president-award.jpg",
      "published_at": "2026-07-11T18:35:58+00:00"
    }
  ]
}
```

With `Accept-Language: ar`, the same request returns:

```json
{
  "data": [
    {
      "id": 3,
      "title": "رئيس نادي لوسيل يحصل على جائزة التميز الرياضي والشبابي",
      "excerpt": "حصل رئيس نادي لوسيل نواف محمد المضاحكة على جائزة التميز الرياضي والشبابي ممثلاً عن مركز شباب سميسمة.",
      "image_url": "https://lusailsc.qa/media/news/president-award.jpg",
      "published_at": "2026-07-11T18:35:58+00:00"
    }
  ]
}
```

- **Sorted newest first** (`published_at` descending). The app renders in the order you send.
- **Only published items.** Drafts and future-dated posts must not appear.

### `GET /content/news/{id}` — one article (news detail page)

Same fields as the list, **plus `body`** (the full article text):

```json
{
  "data": {
    "id": 3,
    "title": "…",
    "excerpt": "…",
    "body": "Full article text…",
    "image_url": "https://lusailsc.qa/media/news/president-award.jpg",
    "published_at": "2026-07-11T18:35:58+00:00"
  }
}
```

- Unpublished or unknown `id` → **404**.
- `body` is plain text. If you intend to send HTML, **tell us first** — the app does not render HTML today.

### `GET /content/fixtures` — upcoming matches

```json
{
  "data": [
    {
      "id": 8,
      "opponent": "Al Rayyan",
      "competition": "Qatar Stars League",
      "is_home": false,
      "venue": "Ahmad bin Ali Stadium",
      "kickoff_at": "2026-07-18T18:35:58+00:00",
      "our_score": null,
      "opponent_score": null
    }
  ]
}
```

- **Upcoming only** (kickoff in the future, no score yet), **soonest first**.
- `is_home` drives the Home/Away badge in the app.

### `GET /content/results` — past results

Identical shape, with scores filled in:

```json
{
  "data": [
    {
      "id": 7,
      "opponent": "Al Arabi",
      "competition": "Qatar Stars League",
      "is_home": true,
      "venue": "Lusail Stadium",
      "kickoff_at": "2026-07-07T18:35:58+00:00",
      "our_score": 2,
      "opponent_score": 2
    }
  ]
}
```

- **Played matches only** (both scores present), **most recent first**.
- `our_score` is always **Lusail's** score, regardless of `is_home`. Do not swap them for away matches.
- In Arabic, `opponent` and `competition` come back translated (`"العربي"`, `"دوري نجوم قطر"`).

### `GET /content/standings` — league table

```json
{
  "data": [
    {
      "position": 1,
      "club_name": "Al Sadd",
      "played": 22,
      "won": 14,
      "drawn": 3,
      "lost": 5,
      "goals_for": 43,
      "goals_against": 18,
      "goal_difference": 25,
      "points": 45,
      "is_own_club": false
    }
  ]
}
```

- **You compute `position`, `goal_difference` and the sort order** — the app renders the array as given. Sort by points, then goal difference, then goals scored.
- **`is_own_club`** must be `true` on the Lusail SC row and nowhere else. The app uses it to highlight the club's row. Do not expect the app to match on the club's name — that breaks the moment the name is spelled differently in Arabic.

## 4. Field rules that will break the app if ignored

These are not style preferences. The app will throw and the screen will fail to load.

| Field | Rule |
|---|---|
| `id` | **Required, integer, never null.** |
| `published_at` (news) | **Required, never null.** Any published article must have one. |
| `kickoff_at` (fixtures/results) | **Required, never null.** |
| All dates | **ISO 8601 with a timezone offset**: `2026-07-11T18:35:58+00:00`. Not `2026-07-11 18:35:58`, not a Unix timestamp. |
| Scores | Integers or `null` — never the strings `"2"` or `""`. |
| `is_home`, `is_own_club` | Real booleans `true`/`false` — never `1`/`0`, never `"true"`. |

Everything else (`title`, `excerpt`, `venue`, `image_url`, …) may be null or absent; the app degrades gracefully.

## 5. Images

Return **absolute URLs** in `image_url`:

```json
"image_url": "https://lusailsc.qa/media/news/president-award.jpg"
```

- `null` is fine — the app renders the card without a photo.
- Images must be reachable over **HTTPS**, publicly, with no auth or hotlink protection.
- The app displays them at **16:9**. Anything wildly off that ratio gets cropped, so a landscape source is best.

## 6. Open questions for the technical call

We should settle these before you build, not after:

1. **Pagination.** No endpoint is paginated today (the demo has a handful of news items). A real site will accumulate hundreds. Decide now whether `/content/news` takes `?page=` / `?limit=`, because retrofitting it later changes the response envelope.
2. **`venue` is not translated** in our current implementation — it comes back in English in both languages. Do you want to translate it? (Our recommendation: yes, since the rest of the match card is translated.)
3. **`body` format** — plain text or HTML? The app renders plain text today; HTML needs work on our side.
4. **Caching** — are you happy for us to cache responses, and will you send `ETag` / `Cache-Control`? The app currently refetches on pull-to-refresh and on a language switch.
5. **Who owns the content editorially** — i.e. which CMS the club's staff actually type into. That's the whole point of this: **one place to enter, two places to display.**

## 7. Reference implementation

Our demo backend implements this contract exactly and the app runs against it today. If it's useful, we can:

- give you real sample responses for every endpoint (they're the JSON above, taken from a live server, not hand-written), and
- point the app at your staging API as soon as one endpoint is up, so you get feedback on the first one before building the rest.

**Suggested first delivery: `/content/news` alone.** We'll wire the app's Home page to it and confirm the shape end-to-end. Then the remaining four are copy-paste.
