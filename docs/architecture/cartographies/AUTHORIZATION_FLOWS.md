# Cartographie des flux d'autorisation

- **Document** : Cartographie des flux d'autorisation
- **Nature** : document transverse du corpus — **n'est pas une ADR**
- **Statut** : VALIDÉE
- **Corpus** : Architecture Horizon v1.0 — GELÉE
- **Référence** : v1.0.1 / d59409b8257f5a725828e281884a1c92e40da7a6
- **Date** : non consignée dans la source
- **Issue de** : ADR-001 à ADR-004
- **Sert de référence à** : ADR-005 (API), ADR-007 (événements), ADR-009 (IA), ADR-010 (données), ADR-011 (sécurité), ADR-015 (CI et tests d'autorisation)
- **Consolidation** : version finale produite à partir du texte initial et des corrections validées

> **[F]** fait vérifié · **[R]** recommandation · **[H]** hypothèse
>
> Ce document est **conceptuel**. Il ne décide aucun SQL, aucune policy ni aucune structure physique définitive.

## 1. Résumé exécutif

Cette cartographie a un objectif de contrôle : **vérifier qu'aucun chemin d'accès aux données ni aucune commande métier n'échappe aux décisions prises**.

Trois constats en ressortent.

**Premier** — les chemins d'accès se réduisent à **quatre familles**, et non dix : accès par `service_role` (WordPress, workers), accès par JWT soumis à RLS (client, chauffeur, partenaire), accès par passerelle à rôle restreint (IA), accès anonyme borné (formulaire public, reçu). Toute surface future doit se rattacher à l'une d'elles ; une cinquième famille serait un signal d'alerte architectural.

**Deuxième** — deux familles échappent à RLS, **mais pour la même raison technique** : elles écrivent avec `service_role`, qui contourne RLS par construction **[F]**.

> **« Le formulaire public n'est pas soumis à RLS parce que WordPress écrit avec `service_role`. L'absence d'acteur explique l'attribution `actor_id = NULL`, mais ne constitue pas la cause du contournement de RLS. »**

L'absence d'acteur explique uniquement trois choses : l'attribution `NULL`, le caractère anonyme de l'initiateur, et la nécessité d'un identifiant de corrélation technique. **Elle n'a aucun effet sur RLS.**

Ces surfaces sont encadrées autrement : confinement, attribution et rotation du côté `service_role` ; validation serveur, limitation de débit et minimisation du côté anonyme. **Le dire explicitement vaut mieux que de laisser croire à une couverture uniforme.**

**Troisième** — un point de contrôle revient dans **toutes** les surfaces authentifiées et constitue le pivot du dispositif : **la résolution dynamique de l'acteur**. L'omettre sur une seule surface annule la révocation immédiate partout ailleurs.

## 2. Chaîne d'autorisation générale

```
 ① identité technique ou humaine
      │
 ② authentification ─── WP Auth │ Supabase Auth │ secret machine │ signature webhook
      │
 ③ ACTEUR CANONIQUE résolu ────────────► absent sur surface authentifiée ⇒ REFUS
      │
 ④ état actif ?  ─────────────────────► désactivé ⇒ REFUS GLOBAL IMMÉDIAT
      │
 ⑤ CONTEXTE d'accès  (intention déclarée, VÉRIFIÉE côté serveur)
      │                                  absent/incompatible ⇒ REFUS
      │                                  restreint, n'élargit jamais
 ⑥ RÔLES relus en base  ∩  contexte ──► rôle non admis dans le contexte ⇒ REFUS
      │
 ⑦ ORGANISATION (appartenance active) ─► n'ouvre à elle seule AUCUNE prestation
      │
 ⑧ AFFECTATION (+ désignation nominative éventuelle, état actif/grâce/clos)
      │                                  absente/close ⇒ REFUS opérationnel
 ⑨ type d'opération
      │
 ⑩ QUALIFICATION par le runtime ── lecture │ écriture directe │ COMMANDE
      │                            (sensibilité décidée ICI, jamais par l'appelant)
      ├── lecture / écriture simple ──► RLS
      └── commande ──► runtime : idempotence → invariants → écriture
      │
 ⑪ AUDIT  (acteur exécutant + corrélation + approbateur si IA)
      │
 ⑫ ÉVÉNEMENT MÉTIER éventuel (même transaction)
```

**Étapes ③ ④ ⑤ ⑩ ⑪ sont obligatoires sur toute surface authentifiée.** Les étapes ⑥ à ⑧ dépendent de la population.

## 3. Frontières par surface

### 3a. Identité et contexte

| Surface | Authentification | Acteur résolu | Contexte attendu | Source des rôles | Source de l'affectation |
|---|---|---|---|---|---|
| **Back-office WordPress** | Session WP + MFA | Humain, via liaison | *(sans objet — hors JWT)* | Base | Base |
| **Formulaire public** | Aucune | **Aucun** — `NULL` | Aucun | — | — |
| **Espace client** | JWT lien magique | Humain | `client`, vérifié serveur | Base | — |
| **PWA chauffeur** | JWT mot de passe | Humain | `chauffeur`, vérifié serveur | Base | **Base — décisive** |
| **Portail partenaire** | JWT lien magique | Humain | `partenaire`, vérifié serveur | Base | **Base — décisive** |
| **Edge Functions** | JWT de l'appelant **revalidé** | Celui de l'appelant | Revalidé | Base | Base |
| **Passerelle IA** | Secret d'agent | **Acteur IA** | `ia` + configuration versionnée | Liste d'outils | — |
| **Webhooks** | **Signature** | Acteur de service dédié | Aucun | — | — |
| **Workers / tâches planifiées** | Secret machine | Acteur de service dédié | Aucun | — | — |
| **Opérations admin privées** | Session WP + MFA | **Humain obligatoire** | *(sans objet)* | Base | — |

### 3b. Accès aux données et audit

| Surface | Accès base | `service_role` | RLS | Écritures possibles | Idempotence | Audit | Si acteur désactivé |
|---|---|---|---|---|---|---|---|
| **Back-office WP** | Via PHP | oui | contournée | Orchestration PHP | Recommandée | Acteur humain | Refus applicatif |
| **Formulaire public** | Via PHP | **oui → cause du contournement** | contournée | Création de demande | Existante **[F]** | `NULL` + corrélation | *(s.o.)* |
| **Espace client** | PostgREST | non | **oui** | Lecture ; écritures sans invariant | Si écriture | Acteur humain | **Refus par RLS** |
| **PWA chauffeur** | PostgREST + commandes | non | **oui** | Commandes | **Obligatoire** | Acteur humain | **Refus par RLS** |
| **Portail partenaire** | PostgREST + commandes | non | **oui** | Commandes | **Obligatoire** | Acteur humain | **Refus par RLS** |
| **Edge Function — catégorie A** | Sous JWT utilisateur | **non** | **oui — reste soumise** | Commandes dans les droits de l'utilisateur | Obligatoire | Acteur de l'appelant | **Refus par RLS** |
| **Edge Function — catégorie B** | Privilégiée | **oui, sous conditions** | contournée | Commandes multi-table, transactionnelles | **Obligatoire** | Acteur de l'appelant ou service | **Refus avant commande** |
| **Passerelle IA** | **Rôle restreint** | **jamais** | **oui** | Propositions | Obligatoire | **Double attribution** | Refus |
| **Webhooks** | Via fonction privilégiée | oui | contournée | Commande / événement | **Obligatoire** | Acteur de service | *(s.o.)* |
| **Workers** | Direct | oui | contournée | Traitement de file | Existante **[F]** | Acteur de service | *(s.o.)* |
| **Admin privées** | Via PHP | oui | contournée | Sensibles | Obligatoire | **Humain obligatoire** | **Refus fermé** |

**Catégorie A — fonction sous identité utilisateur** : reçoit un JWT utilisateur · résout l'acteur · agit avec les droits de l'utilisateur · **reste soumise à RLS** · **ne détient pas `service_role` lorsque cela suffit**.

**Catégorie B — fonction privilégiée** : commande multi-table · webhook externe · opération transactionnelle exigeant un privilège serveur · tâche planifiée · passerelle nécessitant un accès serveur contrôlé. Elle peut utiliser `service_role`, **uniquement après** : authentification ou vérification de signature · résolution de l'acteur ou du service · autorisation explicite · qualification de l'action · idempotence · audit · **réduction du périmètre de la fonction**.

> **Principe : `service_role` n'est utilisé dans une Edge Function que lorsque les droits utilisateur, RLS et les fonctions SQL sécurisées ne permettent pas d'exécuter correctement l'opération.**

## 4. Matrice population × données × opérations

> **« Cette matrice décrit des plafonds d'accès conceptuels et des interdictions certaines. Elle ne constitue pas encore un contrat d'autorisation implémentable. Chaque droit positif devra être confirmé par les ADR-005, 009, 010 et 011. »**

**Qualification des cellules**

| Marque | Signification |
|---|---|
| **⛔** | **Interdiction certaine** — acquise, non renégociable |
| ◐ | Accès **conceptuellement envisagé** — plafond, non confirmé |
| ✔ | Accès **confirmé** par une ADR acceptée |
| ⏱ | Accès **temporaire**, borné dans le temps |
| **?** | **À préciser** — dépend d'un contrat, d'un modèle ou d'une durée non décidés |

**Légende d'opération** — `∅` aucun accès · `L` lecture · `E` écriture simple · `C` commande · `H` acteur humain requis *(action sensible)* · `T` temporaire · `A` par affectation · `P` par propriété · `I` interne seulement

| | Contact | Demande | Commercial | Opérationnel | Mission | Communic. | Audit | Organis. | Membres | Affectat. | Paiement | Config. IA |
|---|---|---|---|---|---|---|---|---|---|---|---|---|
| **Visiteur public** | C | C + L*jeton* | ⛔ | ⛔ | ∅ | ∅ | ∅ | ∅ | ∅ | ∅ | **?** | ⛔ |
| **Administrateur** | L E | L C | L E | L C | L C | L C | **L seule** | L E | L E **H** | C **H** | **?** **H** | L E **H** |
| **Collaborateur** | L E | L C | L E | L C | L C | L C | **L seule** | L | L | C **H** | L | ∅ |
| **Client** | L E *P* | L *P* | L *P* restreint | L *P* | L *P* | L *P* | ∅ | L *P* | ∅ | ∅ | **?** *P* | ⛔ |
| **Chauffeur** | L *A T* minimal | ∅ | **⛔ strict** | L *A* | L *A* + C | L *A* | ∅ | L sienne | ∅ | L *A* | ∅ | ⛔ |
| **Membre partenaire** | L *A T* minimal | ∅ | **⛔ strict** | L *A* | L *A* + C | **?** *A* | ∅ | L sienne | L siens | L *A* | ∅ | ⛔ |
| **Admin d'org. partenaire** | L *A T* | ∅ | **⛔ strict** | L *A* | L *A* + C | **?** *A* | ∅ | L E sienne | **?** portée bornée | L *A* | ∅ | ⛔ |
| **Acteur de service WP** | technique | technique | technique | technique | technique | technique | **E** | technique | technique | technique | technique | ⛔ |
| **Worker** | ∅ hors besoin | L | ∅ | L | L | **E C** | **E** | ∅ | ∅ | ∅ | **?** | ⛔ |
| **Agent IA** | L restreint | L restreint | **selon agent** | L | L | **C → H** | ∅ | ∅ | ∅ | C → **H** | ∅ | **⛔ absolu** |
| **Webhook externe** | ∅ | ∅ | ∅ | ∅ | ∅ | **C** | **E** *via service* | ∅ | ∅ | ∅ | **?** | ⛔ |

**Reclassements en « à confirmer »**

| Élément | Motif |
|---|---|
| Paiements clients | Dépend du prestataire et du parcours (Q-A) |
| Communications partenaires | Quel sous-ensemble, quelle durée |
| Droits des workers sur les paiements | Dépend du modèle de webhook |
| Administration des membres d'une organisation partenaire | Portée exacte à définir |
| Toute écriture simple non rattachée à un contrat API | L'ADR-005 décidera si elle existe |

**Interdictions certaines, acquises**

| # | Interdiction |
|---|---|
| **⛔1** | **Aucune donnée commerciale pour chauffeur et partenaire** |
| **⛔2** | **Aucun accès direct de l'agent IA à sa configuration** (A14) |
| **⛔3** | **Aucune action sensible sans acteur humain résolu** (A5) |
| **⛔4** | **Aucune opération externe sans affectation lorsqu'elle est requise** (A4) |
| **⛔5** | Aucun rôle global produit par une organisation partenaire |
| **⛔6** | Aucune écriture humaine directe dans l'audit |

**Six lectures décisives** — elles portent sur des interdictions, non sur des droits positifs, et restent donc pleinement valides :

1. **`⛔ strict` sur les données commerciales** pour chauffeurs et partenaires — c'est ce qui rend impérative la séparation au niveau des relations (ADR-004 §4).
2. **L'audit n'est jamais écrit par un humain** : seuls les acteurs de service et workers y écrivent. Un humain le lit, jamais ne le modifie.
3. **Les acteurs de service ont un accès « technique »**, non un accès autorisé : ils contournent RLS. Leur encadrement est ailleurs.
4. **Un agent IA n'a aucun accès à sa propre configuration — `⛔ absolu`.** Autrement, il pourrait élargir sa propre liste d'outils. C'est un invariant, pas une préférence.
5. **L'administrateur d'organisation partenaire a une écriture sur ses membres à portée bornée** : il ne peut jamais produire un rôle global Horizon.
6. **Le contact vu par un chauffeur ou un partenaire est minimal et temporaire** — le nécessaire à l'exécution, borné dans le temps.

## 5. Flux A à I

**Flux A — Action interne non sensible**
`Session WP` → acteur humain résolu → rôle interne → autorisation → orchestration PHP → `service_role` → écriture → **audit acteur humain**.
*Échoue si* : liaison absente → repli **mesuré et visible** (alerte + métrique + login conservé).

**Flux B — Action interne sensible sans liaison d'acteur**
`Session WP valide` → **aucun acteur humain résolu** → **REFUS FERMÉ** ou procédure de régularisation.
**Aucun repli silencieux.** Une session WordPress valide n'est pas une autorisation Horizon.

**Flux C — Lecture client**
`JWT` → acteur actif → **contexte `client` vérifié serveur** → liaison contact ↔ acteur → RLS → lecture de ses seules données.
*Échoue si* : contexte absent ou incompatible · acteur désactivé · liaison inexistante.

**Flux D — Commande chauffeur**
`JWT` → acteur actif → contexte `chauffeur` vérifié serveur → rôle chauffeur → **affectation active** → **Edge Function, catégorie A par défaut** *(sous JWT utilisateur, soumise à RLS)* → résolution de l'acteur → qualification par le runtime → clé d'idempotence → invariants → écriture → audit → événement.

**Élévation en catégorie B uniquement si** l'opération est multi-table, transactionnelle, ou exige un privilège que RLS et les fonctions SQL sécurisées ne permettent pas. **L'élévation est une exception justifiée cas par cas, jamais le défaut.**

*Échoue si* : affectation absente, terminée ou en grâce · commande répétée sans clé · invariant violé.

**Flux E — Accès partenaire**
`JWT` → acteur → **contexte `partenaire` vérifié** → appartenance organisationnelle active → **affectation** → désignation nominative éventuelle → **état actif ou grâce** → accès minimal.
*Échoue si* : organisation inactive · désignation ne correspondant pas · état clos.
**Rappel** : l'appartenance seule n'ouvre rien.

**Flux F — Action IA non sensible**
Agent authentifié auprès de la passerelle → acteur IA **actif** → configuration versionnée résolue → outil dans la liste autorisée → **le runtime qualifie l'action** → lecture ou proposition → RLS via **rôle restreint** → audit.

**Flux G — Action IA sensible**
Agent → proposition → **le runtime qualifie « sensible »** → approbation par **acteur humain résolu** → **revérification à l'exécution** (agent toujours actif, action toujours qualifiée de la même façon) → exécution → **double attribution** → événement.
*Échoue si* : agent suspendu entre approbation et exécution · approbateur sans liaison d'acteur · tentative d'outil alternatif pour changer la qualification.

**Flux H — Webhook**
Fournisseur → **vérification de signature** → **anti-rejeu** (horodatage + identifiant) → **idempotence** → acteur de service dédié → commande ou événement → audit.
*Échoue si* : non signé · hors fenêtre · déjà traité *(→ réponse idempotente, pas une erreur)*.

**Flux I — Tâche planifiée**
Runtime de confiance → acteur de service **dédié à cet usage** → secret technique → exécution → audit → événement.
**Jamais** d'acteur de service générique partagé entre usages.

## 6. Refus par défaut

| # | Cas | Détecté à l'étape |
|---|---|---|
| 1 | Acteur absent sur une surface authentifiée | ③ |
| 2 | **Acteur désactivé** | ④ — refus global immédiat |
| 3 | Contexte absent sur une surface qui l'exige | ⑤ |
| 4 | Contexte incompatible avec la population ou l'application | ⑤ — **vérification serveur** |
| 5 | Rôle non admis dans le contexte | ⑥ |
| 6 | Organisation inactive | ⑦ |
| 7 | Affectation absente, terminée ou hors délai | ⑧ |
| 8 | Désignation nominative ne correspondant pas | ⑧ |
| 9 | **Accès commercial depuis un contexte chauffeur** | ⑤ ∩ ⑥ + séparation des relations |
| 10 | **Action sensible sans acteur humain résolu** | ⑩ |
| 11 | Commande répétée sans clé d'idempotence requise | ⑩ |
| 12 | Agent IA suspendu | ③ ④ — **à la proposition ET à l'exécution** |
| 13 | Outil IA non autorisé | passerelle |
| 14 | Webhook non signé ou rejoué | ② + anti-rejeu |
| 15 | **Relation dédiée non vérifiée vis-à-vis de RLS** | **refus de mise en service**, pas d'exécution |
| 16 | Action WordPress sensible sans liaison d'acteur | ⑩ — Flux B |

Le cas 15 est de nature différente : c'est un **refus de déploiement**. Une relation dont le comportement vis-à-vis de RLS n'a pas été vérifié ne doit pas être mise en service.

## 7. Invariants d'autorisation

| # | Invariant | Ce qu'il protège |
|---|---|---|
| **A1** | **Le contexte ne peut que restreindre**, jamais élargir — et il est vérifié côté serveur. | Étape ⑤ · cumul de rôles |
| **A2** | Le rôle seul n'ouvre aucune mission partenaire. | Flux E · ⛔1 |
| **A3** | L'appartenance organisationnelle seule n'ouvre aucune prestation. | Flux E |
| **A4** | L'affectation est nécessaire pour tout accès opérationnel. | Flux D, E · ⛔4 |
| **A5** | Une action sensible exige un acteur humain résolu. | Flux B, G · ⛔3 |
| **A6** | **Un agent IA ne classe jamais lui-même une action** — la qualification appartient au runtime. | Flux F, G · étape ⑩ |
| **A7** | `service_role` contourne RLS et doit rester confiné. | Surfaces privilégiées |
| **A8** | **Une relation dédiée n'est pas une frontière de sécurité.** | Refus 15 · Q-N |
| **A9** | **La résolution dynamique de l'acteur est obligatoire sur toute surface authentifiée.** | Étape ③ · révocation immédiate |
| **A10** | Toute commande externe à effets multiples est idempotente, **garantie côté serveur**. | Flux D, E, H |
| **A11** | Aucune policy formulée en négatif. | Refus par défaut |
| **A12** | Aucune table livrée sans RLS et tests. | Refus par défaut |
| **A13** | Toute action système nouvelle est attribuée à un acteur de service. | Flux I |
| **A14** | **Un agent IA n'a aucun accès à sa propre configuration**, ni en lecture ni en écriture. | ⛔2 |
| **A15** | **Toute fonction privilégiée utilisant `service_role` doit revalider l'identité, résoudre l'acteur ou le service, appliquer l'autorisation et limiter strictement son périmètre. Une fonction pouvant fonctionner sous JWT utilisateur et RLS ne doit pas être élevée sans nécessité.** | Catégories A et B · Flux D |
| **A16** | **L'état métier, l'événement métier et l'attribution nécessaire à l'imputabilité doivent rester cohérents. Les logs techniques et de diagnostic peuvent être produits hors transaction et ne doivent pas bloquer l'opération métier.** | Étapes ⑪ et ⑫ |

**A14 et A15 émergent de la mise en regard des surfaces**, et non d'une ADR prise isolément. C'est l'apport propre de cette cartographie.

## 7 bis. Audit et transaction — quatre objets distincts

| Objet | Nature | Garantie requise |
|---|---|---|
| **Événement métier** | Fait accompli, immuable | **Atomique** avec l'écriture d'état |
| **Audit métier** | Imputabilité d'une écriture | **Même transaction lorsque l'imputabilité de cette écriture l'exige** |
| **Log technique** | Diagnostic, mesure | Hors transaction — **ne doit jamais faire échouer l'opération métier** |
| **Journal de sécurité critique** | Détection, preuve | Mécanisme séparé, **avec sa propre garantie de livraison** |

**Il n'existe donc pas de règle universelle imposant que tout audit soit écrit dans la même transaction.** La distinction est reprise dans les ADR-007 et ADR-012.

## 8. Cas limites

| Cas | Traitement | Statut |
|---|---|---|
| **Collaborateur également chauffeur** | Contexte (A1) : dans le contexte chauffeur, les rôles internes ne s'appliquent pas | Résolu |
| **Partenaire multi-collaborateurs** | Affectation à l'organisation ; désignation nominative pour restreindre | Résolu — **Q-L** sur le seuil |
| **Fin d'affectation pendant une session** | Affectation relue à chaque opération → passage en grâce puis refus, **sans attendre l'expiration du jeton** | Résolu |
| **Acteur désactivé, JWT encore valide** | Résolution dynamique → refus immédiat sur les données. Le jeton reste une preuve d'identité → **révoquer aussi les jetons** (I15) | Résolu |
| **Changement de rôle pendant une session** | Rôles relus en base → effet immédiat | Résolu |
| **Commande mobile rejouée après perte réseau** | Clé d'idempotence → **même résultat, aucun effet dupliqué**, répétition auditée | Résolu |
| **Action IA approuvée avant suspension** | **Revérification à l'exécution** → non exécutée | Résolu |
| **`service_role` compromis** | RLS n'y peut rien. Rotation du secret d'abord, puis les quatre autres étapes | Résolu — **la rotation est le seul levier** |
| **Relation dédiée mal configurée** | **Q-N** — si une relation n'applique pas les policies sous-jacentes, la protection est illusoire | **Ouvert, bloquant** |
| **Contexte falsifié** | Vérification serveur : compatibilité avec application cliente, session, acteur et rôles réels | Résolu par principe — **Q-O** sur le mécanisme |
| **Webhook rejoué** | Anti-rejeu + idempotence → réponse identique, aucun effet | Résolu |
| **Utilisateur WordPress sans acteur** | Non sensible → repli mesuré et visible. Sensible → **refus fermé** | Résolu |
| **Accès partenaire en délai de grâce** | **Lecture seule**, périmètre inchangé, aucune commande | Résolu — **Q-M** sur la durée |
| **Commande reçue après clôture d'affectation** | Refus. **Si elle a été émise hors ligne avant la clôture** : réautorisée à la synchronisation, donc refusée — et le refus doit être **explicite pour l'utilisateur**, non silencieux | Résolu, exigence d'ergonomie |

Le dernier cas mérite attention : un chauffeur ayant agi hors ligne de bonne foi verra son action refusée à la synchronisation. **Le refus doit lui être présenté clairement**, sinon il croira son action enregistrée.

## 9. Dépendances vers les ADR suivantes

| ADR | Ce que cette cartographie lui impose |
|---|---|
| **005 — API** | Quatre familles de chemins d'accès · revalidation obligatoire en Edge Function (A15) · idempotence des commandes externes · surfaces publiques strictement bornées |
| **007 — Événements** | Audit et événement dans la même transaction · corrélation depuis l'anonyme · double attribution IA |
| **009 — IA** | Rôle de base restreint (option B) · **A14** · qualification par le runtime · revérification à l'exécution · matrice des actions sensibles |
| **010 — Données** | **Séparation des relations commerciale / opérationnelle / identifiante** · acteur, rôles, organisation, affectation, désignation, états d'affectation · support de l'idempotence · corrélation |
| **011 — Sécurité** | Matrice des actions sensibles · Q-L, Q-M · MFA · rotation · rétention · contact minimal et temporaire |
| **015 — CI** | **Base PostgreSQL réelle** · tests négatifs par population · test de contexte · test d'acteur désactivé · **test de relation dédiée** · test de rejeu |

## 10. Vérifications techniques encore nécessaires

| Réf | Vérification | Criticité |
|---|---|---|
| **Q-N** | Une relation ou vue dédiée respecte-t-elle les policies de la table sous-jacente ? Identité d'exécution, privilèges du propriétaire, chemins indirects | **Bloquante avant ADR-010** |
| **Q-O** | Le fournisseur peut-il émettre un contexte fiable par application, et comment le valider côté serveur ? | Bloquante avant l'ouverture des surfaces externes |
| — | Capacité de révocation ciblée par appareil | Avant l'Application Chauffeur |
| — | Capacité d'invalidation d'une invitation antérieure | Avant l'ouverture de l'espace client |
| — | Modalités réelles de rotation de `service_role` | Avant toute procédure d'incident |
| **Q1** | Hébergement, cron, proxy | Avant l'ADR-008 |

**Q-N reste la plus urgente** : elle conditionne la faisabilité même de la séparation par relations, sur laquelle repose toute la protection des données commerciales vis-à-vis des chauffeurs et partenaires.

## 11. Questions ouvertes

| Réf | Question |
|---|---|
| **Q-N** | Comportement réel d'une vue ou relation dédiée vis-à-vis de RLS — **bloquante avant ADR-010** |
| **Q-O** | Mécanisme fiable et vérifiable du contexte d'accès |
| **Q-L** | Seuil de taille d'organisation imposant une désignation nominative |
| **Q-M** | Durées exactes des états actif / grâce / clos |
| **Q1** | Capacités réelles de l'hébergement : proxy, IP réelle, cron, cache |

## 12. Conditions de validation

1. Les **quatre familles de chemins d'accès** sont validées comme cadre de référence, toute cinquième famille valant signal d'alerte.
2. La chaîne générale à douze étapes est validée, **③ ④ ⑤ ⑩ ⑪ étant obligatoires** sur toute surface authentifiée.
3. La matrice population × données est validée comme **plafond conceptuel**, avec ses six lectures décisives ; **seules les interdictions sont acquises**, chaque droit positif devant être confirmé par les ADR-005, 009, 010 et 011.
4. Les **seize invariants** sont validés, dont **A14** (aucun accès d'un agent à sa propre configuration), **A15** (fonction privilégiée : revalidation, résolution, autorisation, périmètre réduit) et **A16** (cohérence état / événement / attribution ; logs techniques hors transaction).
5. Les seize cas de refus par défaut sont validés, **le cas 15 étant un refus de déploiement**.
6. Il est acté que **Q-N conditionne l'ADR-010** et que **Q-O conditionne l'ouverture des surfaces externes**.
7. Les Edge Functions sont réparties en **catégories A et B**, l'élévation étant une exception justifiée cas par cas.

---

## Historique de consolidation

| Section | Action | Source de la correction | Résultat |
|---|---|---|---|
| §1, deuxième constat | Remplacement | Correction 1 | Cause du contournement de RLS attribuée à `service_role`, non à l'absence d'acteur |
| §3b | Remplacement intégral | Correction 2 | Edge Functions scindées en catégories A et B ; principe d'usage minimal de `service_role` ajouté |
| §5, Flux D | Remplacement | Correction 2 | Catégorie A par défaut ; élévation en B comme exception justifiée |
| §7, A15 | Remplacement | Correction 2 | Formulation révisée — revalidation, résolution, autorisation, périmètre réduit, non-élévation sans nécessité |
| §7, A16 | Ajout | Correction 3 | Nouvel invariant — cohérence état / événement / attribution ; logs hors transaction |
| §7 bis | Insertion | Correction 3 | Nouvelle section — quatre objets de journalisation distincts |
| §4 | Remplacement intégral | Correction 4 | Matrice qualifiée de plafond conceptuel ; cinq marques de qualification ; cinq reclassements en « à confirmer » ; six interdictions certaines ⛔1–⛔6 |
| §11, §12 | Ajout structurel | Exigence de structure | Sections « Questions ouvertes » et « Conditions de validation » ; contenu repris des §10 et des conditions de passage à l'ADR-005, aucune décision nouvelle |
| §§2, 5 *(hors Flux D)*, 6, 8, 9, 10 | Aucune modification | — | Texte source conservé mot pour mot |

**Point de placement signalé** — La correction 3 (audit et transaction) n'indiquait pas de section cible. Elle avait été présentée sous le libellé « §9 », qui entrait en collision avec la section existante « Dépendances vers les ADR suivantes ». Elle est ici placée en **§7 bis**, immédiatement après l'invariant **A16** qu'elle développe, sans déplacer aucune numérotation existante. **Ce choix porte sur un emplacement, non sur un contenu : aucune décision n'en dépend.** Il peut être modifié sur simple demande.

**Corrections intégrées** : 4.
**Ambiguïtés restant à arbitrer** : aucune sur le fond ; un point de placement signalé ci-dessus.
