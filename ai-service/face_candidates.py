def overlap(a, b):
    x, y, w, h = map(int, a)
    u, v, s, t = map(int, b)
    intersection = max(0, min(x+w, u+s)-max(x, u)) * max(0, min(y+h, v+t)-max(y, v))
    union = w*h+s*t-intersection
    return intersection / union if union > 0 else 0


def confirm_multiple(candidates, confirmations):
    """Keep primary boxes supported by a distinct secondary detection.

    A second Haar cascade reduces false positives, but is not ground truth.
    One-to-one matching prevents a duplicate box being counted twice.
    """
    matches = sorted(((overlap(a, b), i, j) for i, a in enumerate(candidates)
                      for j, b in enumerate(confirmations)), reverse=True)
    kept, used = set(), set()
    for score, i, j in matches:
        if score >= .4 and i not in kept and j not in used:
            kept.add(i)
            used.add(j)
    return [box for i, box in enumerate(candidates) if i in kept]
