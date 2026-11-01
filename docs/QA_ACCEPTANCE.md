# BuntyFun — Release 1 QA acceptance criteria

Scope: homepage, Games, Quizzes, Birthday Wishes, Islamic Names, search, admin dashboard.
A feature doesn't ship until every row in its department's section here passes — fix problems,
don't just report them.

## Desktop (1280px+)

- [ ] Every Release 1 page loads with no console errors
- [ ] Quiz: one question at a time, progress indicator visible, scoring correct, result page
      reachable by its own shareable URL
- [ ] Games: each of the 8 initial games is playable start to finish, replay works
- [ ] Birthday Wishes: filter by relationship + tone returns correct results; copy button
      copies the exact message text; WhatsApp share link opens with the message pre-filled
- [ ] Islamic Names: search, gender filter, alphabetical filter all narrow results correctly;
      every visible entry shows its `source_ref`
- [ ] Site-wide search returns relevant results across departments
- [ ] Admin: draft → review → publish works for each content type; unpublish removes content
      from the public site immediately; every publish/unpublish writes an `audit_logs` row

## Mobile (360–430px)

- [ ] All of the above, with touch targets ≥ 44×44px
- [ ] No horizontal scroll on any Release 1 page
- [ ] Quiz and game interfaces are fully usable one-handed (no interaction requires two
      simultaneous touch points)
- [ ] WhatsApp share opens the WhatsApp app (or wa.me web fallback) correctly on mobile Safari
      and mobile Chrome

## Accessibility

- [ ] Lighthouse Accessibility score ≥ 90 on homepage, one quiz page, one game page, one
      birthday-wishes page, one name-entry page
- [ ] Every interactive control (quiz answer, game control, filter, share button) is reachable
      and operable by keyboard alone
- [ ] Color contrast meets WCAG AA on text and interactive elements
- [ ] Images have meaningful `alt` text; decorative images have empty `alt`
- [ ] Form inputs (search, filters) have associated labels

## Performance

- [ ] Lighthouse Performance score ≥ 85 on mobile for homepage and one representative page per
      department
- [ ] No render-blocking unused CSS/JS shipped to pages that don't need it
- [ ] Images served at appropriate size/format for viewport (no desktop-size images on mobile)

## SEO (ties into Lighthouse SEO + manual checks)

- [ ] Every page has a unique `<title>` and meta description
- [ ] Canonical URL present on every page
- [ ] `sitemap.xml` includes every published entry across all departments
- [ ] `robots.txt` doesn't block anything it shouldn't
- [ ] Urdu routes (`/ur/...`, once that department ships) carry correct `hreflang` and
      `lang="ur" dir="rtl"`

## Security

- [ ] No admin route reachable without the correct role (`canPublish`/`canModerate` checks enforced
      server-side, not just hidden in the UI)
- [ ] CSRF tokens present and verified on every state-changing form
- [ ] All user input (search, filters, quiz answers, admin content forms) is validated server-side
- [ ] `.env`, `storage/*.key`, and any credentials are confirmed absent from the deployed zip's
      publicly-reachable paths
- [ ] Rate limiting in place on search and any public-facing form before Release 2 adds AI Fun
      Studio (abuse surface grows there)
- [ ] No secrets (API keys, DB passwords) committed to git — confirmed by diffing each release
      commit before it's tagged

## Broken links

- [ ] Automated crawl of all Release 1 pages finds zero broken internal links before each release
