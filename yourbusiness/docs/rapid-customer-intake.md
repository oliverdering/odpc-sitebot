# YourBusiness – Rapid Customer Intake (MVP contract)

## Objective
Produce a usable mobile-first customer demo in minutes. Reuse available customer data; never invent contact details or present unverified information as confirmed.

## Acquisition order
1. Explicitly ask permission before using device location. Manual city/address entry must always remain possible.
2. Search company name + locality and ask the operator to choose the correct business; do not silently merge same-name businesses.
3. Accept existing website URL and Google Business Profile/Maps URL as sources. Public information can be extracted where lawful; authenticated Google Business data requires an authorized account/API connection.
4. Accept business card photo, logo and customer-provided photos. Extract name, telephone, address, email, website and business category; show review before applying.
5. Ask only for missing essentials: business name, category, location, primary contact method, WhatsApp consent and publication permission.
6. Offer a brand palette extracted from customer logo/website or operator-selected colors.
7. Generate draft text, service blocks, contact CTA and preview. Mark all inferred claims as drafts.

## Canonical customer record
- id, business_name, category, description, services[]
- location: city, address, coordinates, location_source, location_consent
- contacts: phone_e164, whatsapp_e164, email, website
- online: google_business_url, maps_url, social_urls[]
- branding: logo_asset, palette{primary,secondary,background,text}, photo_assets[]
- business: hours, service_area, legal_name, imprint, privacy_policy
- provenance: each field {value, source, confidence, confirmed_by_operator, updated_at}
- permissions: location, external_lookup, image_use, whatsapp, publish
- demo: slug, draft_status, generated_at, expires_at

## UX
Single mobile screen with three stages:
A. Discover: business name or website or business card upload; optional geolocation.
B. Verify: prefilled data, clear source badges, inline corrections, photo/logo selection and color controls.
C. Preview: responsive preview and one-click draft refresh; separate explicit publish action.

## Rules
- Never require location access; always offer manual entry.
- No automatic use of private Google account data without authorization.
- Never copy protected third-party imagery or text indiscriminately.
- WhatsApp button only if the destination number is confirmed and normalized to E.164; use https://wa.me/<digits> with an appropriate customer inquiry message.
- Do not publish personal contact data or uploaded photos without permission.
- No live payment, domain purchase or public publication during a demo without explicit confirmation.
- Never touch the primary ODPC WordPress installation or database.
- Build in isolated YourBusiness namespace, with safe preview/draft mode first.

## Acceptance criteria
1. Operator can create a draft with business name, city and category.
2. Existing site and uploaded business card can prefill fields, with visible provenance.
3. Google Business discovery is implemented through an authorized provider; absent provider is clearly labeled unavailable, not simulated.
4. Colors, logo and photos are selectable before preview.
5. WhatsApp CTA opens correct confirmed customer number.
6. Preview is mobile-first; changes do not require a full setup restart.
7. Explicit publish action separate from draft generation.
