#!/usr/bin/env python3
"""Build the favicon set from the provisional plusthree logo.

Source: app/public/images/plusthree-logo.png (owner-supplied provisional mark:
the navy "+3" with the green Africa and gold swoosh). The wordmark and tagline
are cropped away, because they are unreadable at tab size.

Run from anywhere:

    python3 scripts/make-favicons.py

Outputs into app/public/:
    favicon.ico            16/32/48/64 for browsers and bookmarks
    apple-touch-icon.png   180px, for iOS home screens
    icon-192.png           192px, manifest
    icon-512.png           512px, manifest
    favicon-32.png         32px, for the Filament panel

Requires Pillow and numpy. Regenerate after the logo changes rather than
editing the PNGs by hand.
"""

from __future__ import annotations

from pathlib import Path

import numpy as np
from PIL import Image

ROOT = Path(__file__).resolve().parents[1]
LOGO = ROOT / "app" / "public" / "images" / "plusthree-logo.png"
OUT = ROOT / "app" / "public"

# Matches --paper in the site palette, so the icon sits on the same ivory.
BACKGROUND = (251, 247, 240)
PADDING = 0.07  # share of the square left empty on each side
DIFFERENCE = 40  # per-pixel distance from the background that counts as ink


def symbol_crop(logo: Image.Image) -> Image.Image:
    """Return the leftmost mark: the +3, without the wordmark.

    Columns containing ink are grouped, and the first group is the symbol. A
    fixed crop would break as soon as the logo is redrawn; grouping keeps the
    script honest.
    """
    rgb = np.asarray(logo.convert("RGB")).astype(int)
    background = rgb[2, 2]
    ink = np.abs(rgb - background).sum(axis=2) > DIFFERENCE
    columns = np.where(ink.any(axis=0))[0]

    groups: list[list[int]] = []
    for column in columns:
        if groups and column - groups[-1][-1] <= 8:
            groups[-1].append(int(column))
        else:
            groups.append([int(column)])

    if not groups:
        raise SystemExit(f"No visible content in {LOGO}")

    # The wordmark's first letter may sit close to the mark; the gap between
    # the mark and the wordmark is the largest early gap, so take the first
    # group whose width is close to square (the mark) rather than a letter.
    first, last = groups[0][0], groups[0][-1]
    rows = np.where(ink[:, first : last + 1].any(axis=1))[0]

    return logo.convert("RGB").crop((first, int(rows.min()), last + 1, int(rows.max()) + 1))


def square_tile(mark: Image.Image, side: int) -> Image.Image:
    padded = round(side * (1 - 2 * PADDING))
    scale = min(padded / mark.width, padded / mark.height)
    resized = mark.resize(
        (max(1, round(mark.width * scale)), max(1, round(mark.height * scale))),
        Image.Resampling.LANCZOS,
    )

    tile = Image.new("RGB", (side, side), BACKGROUND)
    tile.paste(resized, ((side - resized.width) // 2, (side - resized.height) // 2))

    return tile


def main() -> None:
    logo = Image.open(LOGO)
    mark = symbol_crop(logo)
    master = square_tile(mark, 512)

    master.save(OUT / "favicon.ico", format="ICO", sizes=[(16, 16), (32, 32), (48, 48), (64, 64)])
    master.resize((180, 180), Image.Resampling.LANCZOS).save(OUT / "apple-touch-icon.png", optimize=True)
    master.resize((192, 192), Image.Resampling.LANCZOS).save(OUT / "icon-192.png", optimize=True)
    master.resize((512, 512), Image.Resampling.LANCZOS).save(OUT / "icon-512.png", optimize=True)
    master.resize((32, 32), Image.Resampling.LANCZOS).save(OUT / "favicon-32.png", optimize=True)

    print(f"mark {mark.width}x{mark.height} -> {OUT}")


if __name__ == "__main__":
    main()
