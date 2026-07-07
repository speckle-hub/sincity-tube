# SinCity — Design System

## Color Palette

```
Primary Background:    #0A0A0F  (near-black with blue undertone)
Secondary Background:  #12121A  (card surfaces)
Tertiary Background:   #1A1A28  (hover states, inputs)
Border/Lines:          #2A2A3E  (subtle dividers)

Primary Accent:        #DC143C  (crimson red — CTAs, active states)
Secondary Accent:      #FF2D55  (neon pink — highlights, hover glow)
Tertiary Accent:       #FF6B8A  (light pink — secondary highlights)

Neon Cyan:             #00F0FF  (cyan — tags, badges, links)
Neon Purple:           #7B2FF7  (purple — premium/featured badge)
Neon Amber:            #FFB300  (amber — ratings, warnings)

Text Primary:          #F0F0F5  (headings)
Text Secondary:        #A0A0B8  (body)
Text Muted:            #606078  (meta, timestamps)

Success:               #00E676  (verified, checkmarks)
Error:                 #FF1744  (errors, NSFW warnings)
Warning:               #FF9100  (age warnings)
```

---

## Typography

| Element | Font | Weight | Size | Case |
|---------|------|--------|------|------|
| Logo/Wordmark | Cinzel Decorative | 700 | 2.5rem | Uppercase |
| H1 | Cinzel | 700 | 2.25rem | Uppercase |
| H2 | Cinzel | 600 | 1.5rem | Title Case |
| H3 | Inter | 600 | 1.125rem | Title Case |
| Body | Inter | 400 | 1rem | Normal |
| Small/Meta | Inter | 400 | 0.75rem | Uppercase |
| Tags/Badges | Inter | 500 | 0.7rem | Uppercase |
| Navigation | Inter | 500 | 0.85rem | Uppercase |

**Font Sources:**
- Cinzel / Cinzel Decorative — Google Fonts (headings, logo)
- Inter — Google Fonts (body, UI)

---

## Logo Design (AI Image Prompts)

### Logo Concept: SinCity Wordmark

```
Prompt for Midjourney/DALL-E/SD:

"Cinematic logo design for 'SinCity' adult entertainment brand. 
Dark futuristic cyberpunk aesthetic. Text 'SINCITY' in bold 
uppercase serif font (Cinzel-inspired), colored in crimson red 
(#DC143C) with neon cyan (#00F0FF) glow outline. Behind the text, 
a stylized city skyline silhouette in dark gradient with a 
seductive female silhouette emerging from neon-lit streets. 
Gritty, premium, vice city feel. Black background with subtle 
grid lines. High contrast, cinematic lighting. --ar 16:9 --v 6"
```

### Logo Variation — Icon Only

```
Prompt: "Minimalist icon for 'SinCity' brand. A stylized 
combination of a seductive female eye combined with a city 
skyline silhouette. Neon crimson and cyan colors. Sharp, 
geometric, cyberpunk style. Black background. Suitable for 
favicon and app icon. High contrast. --ar 1:1 --v 6"
```

### Logo Variation — Badge/Emblem

```
Prompt: "Circular emblem badge for 'SinCity' adult brand. 
Outer ring with text 'SINCITY' in uppercase serif font. 
Center features a stylized heart with neon glow, partially 
cracked/rebellious design. Inside the heart, a subtle 'XXX' 
symbol. Dark theme with crimson, neon pink, and gold accents. 
Premium nightclub aesthetic. --ar 1:1 --v 6"
```

---

## UI Components

### Buttons

```
Primary CTA:
  BG: linear-gradient(135deg, #DC143C, #FF2D55)
  Text: White, Inter 600, 0.9rem
  Border: none
  Hover: glow effect (box-shadow: 0 0 15px rgba(220,20,60,0.6))
  Border-radius: 6px
  Padding: 12px 28px

Secondary:
  BG: transparent
  Text: #F0F0F5
  Border: 1px solid #2A2A3E
  Hover: border-color #DC143C, text #DC143C

Premium Badge:
  BG: linear-gradient(135deg, #7B2FF7, #DC143C)
  Text: White, 0.7rem, uppercase
  Border-radius: 4px
  Padding: 4px 10px
```

### Cards (Video Thumbnails)

```
Container:
  BG: #12121A
  Border: 1px solid #2A2A3E
  border-radius: 8px
  overflow: hidden
  transition: transform 0.2s, box-shadow 0.2s

Thumbnail:
  Aspect ratio: 16:9
  Overlay: gradient bottom-to-top (transparent → #0A0A0F)
  Duration badge: bottom-right, BG #0A0A0F + alpha 0.8
  Hover: scale(1.03), box-shadow 0 8px 25px rgba(0,0,0,0.5)

Title:
  Text: Inter 500, 0.85rem, #F0F0F5
  Line-clamp: 2

Meta:
  Views: Inter 400, 0.75rem, #606078
  Rating: star icon + number, amber #FFB300
```

### Navigation

```
Top Bar:
  BG: #0A0A0F
  Border-bottom: 1px solid #2A2A3E
  Height: 60px
  Sticky: true

Items:
  Text: Inter 500, 0.85rem, uppercase
  Color: #A0A0B8 (default) → #F0F0F5 (hover)
  Active: bottom border 2px solid #DC143C

Search Input:
  BG: #1A1A28
  Border: 1px solid #2A2A3E (focus: #DC143C)
  Text: #F0F0F5
  Placeholder: #606078
  Icon: search glass, neon cyan
```

### Hero Banner (Homepage)

```
Full-width, min-height 60vh
BG: dark gradient with city skyline silhouette overlay
Center content:
  - Tagline: "WELCOME TO SINCITY" (Cinzel, 3rem, uppercase, crimson gradient)
  - Subtitle: "Where Sin Meets Pleasure" (Inter, 1.25rem, #A0A0B8)
  - CTA button: "Enter the City" → reveals content

Background: animated particle effect (subtle neon dots/rain)

Optional: rotating video loop (muted, dark filter overlay)
```

---

## Thumbnail Style Guide

Maintain consistent thumbnail aesthetics:

- **Normal Porn**: Warm tones, high contrast, focus on faces/expressions
- **Hentai**: Vibrant saturated colors, cel-shaded anime aesthetic
- **JAV**: Clean, bright studio lighting, idol-centric

All thumbnails should have:
- 16:9 aspect ratio
- Minimum 720p resolution (1280x720)
- Dark vignette overlay
- SinCity watermark (bottom-right, 10% opacity)
- Duration badge
- No source site logos/branding (remove before upload)

---

## Homepage Mockup (Text Description)

```
┌─────────────────────────────────────────────────────────────┐
│ [LOGO]  NORMAL  HENTAI  JAV  TRENDING  [🔍 SEARCH]  [👤] │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│   ╔═══════════════════════════════════════════════════════╗  │
│   ║  ░░░░░ HERO BANNER ░░░░░                            ║  │
│   ║  ░░░░░ City skyline + figure ░░░░░░                 ║  │
│   ║  ░░░░░ "WELCOME TO SINCITY" ░░░░░░░                ║  │
│   ║  ░░░░░ [Enter the City] ░░░░░░░░░░░░░              ║  │
│   ╚═══════════════════════════════════════════════════════╝  │
│                                                             │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐                  │
│  │ NORMAL   │  │ HENTAI   │  │ JAV      │                  │
│  │ Category │  │ Category │  │ Category │                  │
│  │ Card     │  │ Card     │  │ Card     │                  │
│  └──────────┘  └──────────┘  └──────────┘                  │
│                                                             │
│  🔥 TRENDING NOW ──────────────────────────────────→       │
│  [Thumb][Thumb][Thumb][Thumb][Thumb][Thumb]                 │
│  [Thumb][Thumb][Thumb][Thumb][Thumb][Thumb]                 │
│                                                             │
│  📺 LATEST VIDEOS ──────────────────────────────────→      │
│  [Thumb][Thumb][Thumb][Thumb][Thumb][Thumb]                 │
│  [Thumb][Thumb][Thumb][Thumb][Thumb][Thumb]                 │
│  [Thumb][Thumb][Thumb][Thumb][Thumb][Thumb]                 │
│                                                             │
│  🎌 JAV HIGHLIGHTS ──────────────────────────────────→     │
│  [Thumb][Thumb][Thumb][Thumb]                               │
│                                                             │
│  🎨 HENTAI WEEKLY ───────────────────────────────────→     │
│  [Thumb][Thumb][Thumb][Thumb]                               │
│                                                             │
├─────────────────────────────────────────────────────────────┤
│ DMCA | Privacy | 2257 | Terms | Contact | © 2026 SinCity   │
└─────────────────────────────────────────────────────────────┘
```
