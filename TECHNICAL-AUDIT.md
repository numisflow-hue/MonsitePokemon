# Audit technique — CoolPokemonGames

Date : **1er septembre 2026**
Version auditée : branche `main`, commit publié `379ab3f`

## Conclusion

Le Pokédex fonctionne localement et sa base de données est exploitable. La recherche, les filtres, les fiches, les évolutions, le son et le mode Shiny répondent correctement. L'affichage est également utilisable sur mobile sans débordement horizontal.

Le site n'est toutefois pas prêt pour une indexation publique. Les priorités sont : rétablir un HTTPS stable, corriger les réponses des routes inconnues, sécuriser les paramètres d'URL et lever le blocage volontaire des moteurs de recherche au moment de la mise en ligne.

## Suivi des corrections

- **Corrigé localement le 1er septembre 2026 :** validation stricte de `lang`, `sort` et `type`.
- **Corrigé localement le 1er septembre 2026 :** échappement systématique des valeurs dynamiques injectées dans le HTML et construction sûre des URL.
- **Corrigé localement le 1er septembre 2026 :** vraie réponse HTTP 404 avec une page légère et traduite dans les six langues.
- **Corrigé localement le 1er septembre 2026 :** URL multilingues lisibles (`/fr/herbizarre`, `/en/ivysaur`) et redirections permanentes depuis les anciennes URL à paramètres.
- **Amélioré localement le 1er septembre 2026 :** page 404 plus visuelle et ludique, avec message Pokémon traduit.
- **Amélioré localement le 1er septembre 2026 :** remplacement des drapeaux émojis, mal rendus sous Windows, par un menu de langues HTML avec six drapeaux dessinés en CSS, sans JavaScript ni ressource externe.
- Ces corrections ont passé la syntaxe PHP, les tests HTTP et les parcours de non-régression dans le navigateur. Elles ne sont pas encore publiées sur GitHub ni déployées chez Hostinger.

## Ce qui est validé

- Syntaxe PHP valide avec PHP 8.2.12.
- JSON valide : **1 025 Pokémon**, identifiants 1 à 1025, sans doublon d'identifiant ou de nom anglais.
- Aucun nom, description, visuel, Shiny, cri ou bloc de statistiques manquant dans les six langues affichées.
- Accueil, recherche clavier, filtre par type, tri, changement de langue et fiches accessibles localement.
- Famille d'évolution et liens entre ses membres fonctionnels sur la fiche de Pikachu.
- Bouton Shiny fonctionnel ; aucune erreur ou alerte relevée dans la console du navigateur.
- Mise en page mobile testée à 390 × 844 px : deux cartes par ligne et aucun défilement horizontal.
- Aucun avertissement PHP relevé pendant les parcours testés.

## Priorité 0 — avant mise en ligne publique

### 1. Stabiliser le domaine et HTTPS

- La zone Cloudflare `coolpokemongames.com` est active et utilise bien `shaz.ns.cloudflare.com` et `zod.ns.cloudflare.com`.
- L'apex proxifié pointe vers l'origine Hostinger `145.223.32.51` et `www` pointe vers l'apex.
- Universal SSL est activé, mais sa vérification est encore `pending_validation`.
- Le site public `www` renvoie encore une erreur `502 Bad Gateway` au moment du contrôle.
- DNSSEC reste `pending`. Le mauvais DS Dynadot a été supprimé ; ne rien réactiver avant stabilisation. Le DS courant affiché par Cloudflare porte le Key Tag `2371`, mais il faudra reprendre les valeurs Cloudflare au moment précis de la réactivation.
- Le mode SSL Cloudflare est `full`. `Always Use HTTPS` reste désactivé : ne l'activer qu'une fois le certificat et l'origine vérifiés.

### 2. Renvoyer une vraie erreur 404

Dans la version publiée `379ab3f`, une URL inconnue renvoie le code `200` et toute la page d'accueil. La version de travail renvoie maintenant `404` avec une page d'environ 6 Ko.

Correction attendue : distinguer explicitement l'accueil, une fiche existante et une route inconnue ; appeler `http_response_code(404)` et afficher une petite page 404 pour le dernier cas.

### 3. Sécuriser tous les paramètres et sorties HTML

Ce risque existe dans la version publiée `379ab3f`. Il est corrigé dans la version de travail par des listes de valeurs autorisées, une construction centralisée des URL et l'échappement HTML.

Correction attendue :

- autoriser uniquement `sort=id|name` et les 18 slugs de type connus ;
- utiliser `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')` pour toute valeur injectée dans le HTML ;
- appliquer aussi l'échappement aux contenus issus de `pokedex.json`, même si ce fichier est actuellement maîtrisé.

### 4. Décider quand ouvrir l'indexation

`robots.txt` contient actuellement `Disallow: /` et bloque donc tous les robots. C'est acceptable pendant une phase privée, mais bloquant au lancement.

Au lancement seulement : remplacer cette règle, ajouter un `sitemap.xml`, puis contrôler l'indexation dans Google Search Console.

## Priorité 1 — qualité, SEO et performance

### Performance de l'accueil

- Réponse HTML de l'accueil : **965 636 octets** avant compression.
- **1 025 cartes**, **1 026 images déclarées** et environ **8 281 nœuds DOM**.
- Les images sont en chargement différé, ce qui aide, mais tout le HTML des 1 025 cartes est quand même généré et envoyé.

Recommandation : pagination serveur ou bouton « Charger plus », par lots d'environ 30 à 60 Pokémon. Conserver les filtres côté serveur et limiter la recherche immédiate aux cartes chargées, ou créer ensuite une recherche dédiée.

### SEO des pages

Les fiches n'ont actuellement ni meta description, ni URL canonique, ni `hreflang`, ni données structurées, ni métadonnées Open Graph. Le titre est seulement le nom du Pokémon. Le bandeau et la fiche utilisent aussi chacun un `h1`.

Recommandation : définir un modèle SEO multilingue commun aux fiches, ajouter une seule hiérarchie de titres, les URL canoniques et un sitemap.

### Accessibilité et HTML

- Sur l'accueil, 1 025 images Pokémon sur 1 026 n'ont pas de texte alternatif.
- Le champ de recherche et les listes déroulantes utilisent leur apparence ou leur placeholder sans libellé explicite.
- Les cartes imbriquent un lien de type dans le lien principal de la carte via `<object>`, structure interactive fragile et non conforme.
- Plusieurs pastilles claires, notamment Électrik, ont probablement un contraste insuffisant avec le texte blanc.
- Les boutons ont un texte visible, mais leurs états actif/focus et leurs intitulés accessibles peuvent être renforcés.

### Tri localisé

Le tri `Nom (A-Z)` utilise `strcmp`. En français, les caractères accentués ne sont donc pas classés comme l'attend un lecteur : par exemple `Démolosse` peut arriver après `Dracaufeu`.

Recommandation : utiliser `Collator` de l'extension PHP Intl si elle est disponible, avec une solution de repli documentée.

## Priorité 2 — préparation de la suite

- Les images et les cris dépendent directement de `raw.githubusercontent.com`. Prévoir une stratégie locale ou un CDN maîtrisé pour éviter qu'un changement tiers casse toutes les fiches.
- Le code entier est dans `index.php`. Avant d'ajouter de nombreux quiz et des comptes, séparer au minimum le chargement des données, le routage, les vues et les ressources CSS/JavaScript.
- Ajouter quelques tests automatiques : validation du JSON, code HTTP des routes, rendu d'une fiche, langues et paramètres invalides.
- Relever ensuite de vrais Core Web Vitals sur l'URL publique. L'outil Chrome DevTools nécessaire à une trace LCP/CLS/TBT formelle n'était pas disponible pendant cet audit ; aucun score Lighthouse n'a donc été inventé.
- Avant les futurs comptes et progressions, choisir explicitement l'hébergement PHP, la base de données, le système d'authentification et la gestion des données personnelles.

## Ordre de correction recommandé

1. Corriger localement validation/échappement et vraie page 404.
2. Corriger la structure HTML, les textes alternatifs et les libellés.
3. Ajouter pagination/chargement progressif.
4. Ajouter le socle SEO sans encore ouvrir `robots.txt`.
5. Tester à nouveau, publier sur GitHub puis déployer sur l'hébergement PHP.
6. Vérifier le domaine, le certificat et l'origine ; activer ensuite HTTPS forcé.
7. Ouvrir `robots.txt`, publier le sitemap, puis seulement réactiver DNSSEC avec les valeurs Cloudflare alors affichées.
