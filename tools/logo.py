"""Turn the supplied logo JPGs into transparent PNGs for the site.

The café supplied one logo per theme as an opaque 2000x2000 JPG: black
background with white artwork for dark mode, and the exact inverse for light.
Shipped as-is each would paint its own square background over the page, so the
JPGs are unusable in a header — hence this script rather than a plain copy.

Both files are pure black-and-white, so luminance *is* the coverage map:

    dark  — alpha = luminance,       ink white  (black background falls away)
    light — alpha = 255 - luminance, ink black  (white background falls away)

Taking alpha from luminance rather than keying an exact colour keeps the
antialiased edges of the calligraphy smooth instead of stairstepped.

One consequence worth knowing: the artwork is two-tone, so the *inner* details
(the ring inside the outline, and the calligraphy itself) fall away with the
background and show the page through. That is what makes the mark theme-aware
instead of a sticker — on the dark page the calligraphy reads deep blue, on the
light page it reads white.

The source square is mostly empty, so the content is cropped to its bounding
box before scaling; otherwise every layout would have to compensate for the
padding baked into the file.

    python3 tools/logo.py
"""

from PIL import Image

SOURCES = {
    # name           source file            ink        alpha from
    'logo-dark': ('darkmodelogo.jpg', (255, 255, 255), lambda lum: lum),
    'logo-light': ('lightmodelogo.jpg', (0, 0, 0), lambda lum: lum.point(lambda v: 255 - v)),
}

# Wide enough for the 2x of the largest on-screen use (the home header, 132px)
# with room to spare, and small enough to stay a rounding error on the page.
TARGET_WIDTH = 640

# A hair of breathing room so the outermost stroke is never clipped by rounding.
MARGIN = 8


def convert(name: str, source: str, ink: tuple[int, int, int], alpha_of) -> None:
    image = Image.open(f'refrence/{source}').convert('L')

    alpha = alpha_of(image)

    # getbbox() finds the non-zero region, which after the alpha flip is exactly
    # the artwork — so both themes crop identically and stay aligned.
    box = alpha.getbbox()
    left, top, right, bottom = box
    left, top = max(0, left - MARGIN), max(0, top - MARGIN)
    right, bottom = min(image.width, right + MARGIN), min(image.height, bottom + MARGIN)

    alpha = alpha.crop((left, top, right, bottom))

    height = round(alpha.height * TARGET_WIDTH / alpha.width)
    alpha = alpha.resize((TARGET_WIDTH, height), Image.LANCZOS)

    out = Image.new('RGBA', alpha.size, ink + (0,))
    out.putalpha(alpha)
    out.save(f'public/images/{name}.png', optimize=True)

    print(f'{name}.png  {TARGET_WIDTH}x{height}  from {source} crop {box}')


if __name__ == '__main__':
    for name, (source, ink, alpha_of) in SOURCES.items():
        convert(name, source, ink, alpha_of)
