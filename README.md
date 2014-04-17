chyrp-alphabetize
=================

Sort the blog index alphabetically. The alphabetical index can be viewed at "/alphabetical/" (with clean URLs) or "/?action=alphabetical" (without clean URLs). You can create a link to the alphabetical index using the following Twig code:

    {% if alphabetize %}<a href="$alphabetize">A-Z</a>{% endif %}
