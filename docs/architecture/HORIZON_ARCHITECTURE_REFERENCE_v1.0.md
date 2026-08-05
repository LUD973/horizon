# Référence architecturale Horizon v1.0

- **Document** : Référence architecturale officielle du projet Horizon
- **Nature** : document de **synthèse** du corpus — **n'est pas une ADR**
- **Statut** : RÉFÉRENCE
- **Corpus de référence** : Architecture Horizon v1.0 — **GELÉE**
- **Base** : ADR-001 à ADR-015 (ACCEPTÉES) + Cartographie des flux d'autorisation (VALIDÉE)
- **Référence de dépôt** : v1.0.1 / `d59409b8257f5a725828e281884a1c92e40da7a6`

---

## Avertissement de lecture — portée et autorité de ce document

> **Ce document est une synthèse. Il ne crée aucune décision, ne remplace aucune ADR et ne modifie aucun invariant.**

| En cas de | L'autorité est |
|---|---|
| Divergence entre ce document et une ADR | **L'ADR** |
| Divergence entre deux ADR | **Aucune n'est arbitrée ici** — la contradiction est signalée au §22 et renvoyée aux ADR concernées |
| Question non traitée ici | **Le corpus** — ce document n'est pas exhaustif au niveau du détail |

**Règle de non-arbitrage** : lorsque deux ADR paraissent se contredire, la présente référence **signale** la contradiction et **renvoie** aux textes sources. Elle ne tranche jamais.

**Conventions du corpus, reprises ici** : **[F]** fait vérifié dans le dépôt · **[R]** recommandation · **[H]** hypothèse à confirmer.

**Documents compagnons** :
- [`MATRIX_INVARIANTS.md`](MATRIX_INVARIANTS.md) — matrice exhaustive des invariants
- [`ARCHITECTURE_GLOSSARY.md`](ARCHITECTURE_GLOSSARY.md) — glossaire unique
- [`cartographies/AUTHORIZATION_FLOWS.md`](cartographies/AUTHORIZATION_FLOWS.md) — cartographie des flux d'autorisation

---

## 1. Objet du corpus

### 1.1 Composition

Le corpus architectural Horizon v1.0 comprend **seize documents** :

| # | Document | Nature | Statut |
|---|---|---|---|
| 1 | ADR-001 — Runtimes et architecture générale | ADR | ACCEPTÉE |
| 2 | ADR-002 — Modèle d'identité et populations | ADR | ACCEPTÉE |
| 3 | ADR-003 — Authentification | ADR | ACCEPTÉE |
| 4 | ADR-004 — Autorisation et RLS | ADR | ACCEPTÉE |
| 5 | ADR-005 — Architecture API | ADR | ACCEPTÉE |
| 6 | ADR-006 — Contrat d'extensibilité | ADR | ACCEPTÉE |
| 7 | ADR-007 — Architecture événementielle | ADR | ACCEPTÉE |
| 8 | ADR-008 — Architecture d'exécution | ADR | ACCEPTÉE |
| 9 | ADR-009 — Architecture IA | ADR | ACCEPTÉE |
| 10 | ADR-010 — Modèle de données | ADR | ACCEPTÉE |
| 11 | ADR-011 — Sécurité, confidentialité et conservation | ADR | ACCEPTÉE |
| 12 | ADR-012 — Observabilité et audit | ADR | ACCEPTÉE |
| 13 | ADR-013 — Exploitation et continuité de service | ADR | ACCEPTÉE |
| 14 | ADR-014 — Déploiement et gestion des environnements | ADR | ACCEPTÉE |
| 15 | ADR-015 — Qualité, tests et gouvernance CI/CD | ADR | ACCEPTÉE |
| 16 | Cartographie des flux d'autorisation | Document transverse | VALIDÉE |

Un **dix-septième document** est référencé par le corpus mais **absent du dépôt** : la *Cartographie conceptuelle du modèle d'identité*, qui porte les invariants **I1 à I19**. Voir §22 et §25.

### 1.2 Ce que le corpus décide, et ce qu'il ne décide pas

**Il décide** : l'orientation de runtime · le modèle d'identité conceptuel · les mécanismes d'authentification par population · la frontière d'autorisation · les surfaces d'API et leurs contrats · les règles d'extensibilité · le mécanisme événementiel · le lieu d'exécution de chaque traitement · le cadre de l'IA · les zones et agrégats du modèle de données · les règles de sécurité et de conservation · les catégories de traces · les règles d'exploitation et de continuité · les règles de déploiement · les exigences de qualité.

**Il ne décide pas** : aucun SQL · aucune policy · aucune structure physique définitive · aucune valeur numérique de rétention *(paramètres de politique — **SEC1**)* · aucun prestataire externe · aucune procédure opératoire détaillée.

### 1.3 Base factuelle

Toutes les ADR sont adossées à un audit du dépôt à `d59409b` (v1.0.1, plugin 0.8.1, thème 0.2.0). Les mentions **[F]** renvoient à des faits vérifiés lors de cet audit.

---

## 2. Vision générale

*Source : ADR-001*

### 2.1 Point de départ

L'architecture initiale comporte **un seul runtime applicatif : WordPress**. Toutes les données transitent par un client PHP s'authentifiant auprès de Supabase avec la clé `service_role`, laquelle contourne RLS par construction. **L'intégralité de l'autorisation est portée par du code PHP** **[F]**.

Ce dispositif est cohérent pour le périmètre du Sprint 1 : un visiteur anonyme dépose une demande, une équipe interne la traite depuis l'administration WordPress. Les couches `Support / Data / Domain / Application / Api / PublicSite / Admin` sont étanches, et `Domain/` n'a aucune dépendance WordPress **[F]**.

### 2.2 Le problème posé

**Où s'exécute le code des futurs modules, et quelle instance décide de ce qu'un utilisateur a le droit de voir et de faire ?**

Les modules cibles — CRM étendu, Portail partenaires, Application Chauffeur, Application Client, Conciergerie, agents IA — partagent une caractéristique que l'architecture initiale ne sait pas servir : **des utilisateurs identifiés, extérieurs à WordPress, ne devant accéder qu'à une fraction des données**.

Trois manques constatés à l'audit **[F]** : aucune API authentifiée · aucun modèle d'identité exploitable côté données · aucun contrat d'extension.

### 2.3 La trajectoire retenue

**Architecture hybride incrémentale** — voir §4. WordPress conserve intégralement son périmètre ; Supabase Auth devient la source d'identité canonique ; RLS devient la frontière de sécurité des consommateurs externes ; un second runtime est introduit **uniquement** lorsqu'un besoin concret l'exige.

### 2.4 Positionnement produit, contrainte non technique

*Source : ADR-009 §2*

> « La technologie reste discrète. Le client choisit French Class Prestige. »

Cette contrainte d'ADN est la plus structurante du volet IA : pour une Maison dont le positionnement repose sur la relation humaine, exposer un agent conversationnel à un client **n'est pas d'abord un risque de sécurité, c'est un risque de marque**.

---

## 3. Principes fondateurs

### 3.1 Les neuf principes directeurs de gouvernance

*Source : ADR-001 §4*

| # | Principe |
|---|---|
| **P1** | Ne pas réécrire le socle sans justification forte |
| **P2** | Préserver le domaine PHP pur |
| **P3** | Ne pas faire de WordPress l'unique frontière de sécurité des futurs modules |
| **P4** | Ne jamais exposer `service_role` au navigateur, aux applications ou aux agents IA |
| **P5** | Introduire identité et autorisation avant les modules externes |
| **P6** | Évolution incrémentale et réversible |
| **P7** | Pas de microservices prématurés |
| **P8** | Architecture exploitable par une petite équipe |
| **P9** | Montée en charge prévue sans surdimensionner le MVP |

### 3.2 Les contraintes de cadrage

*Source : ADR-001 §4*

| Réf | Contrainte |
|---|---|
| **Q2** | Chauffeur et Client démarrent en **PWA / web responsive installable**. Le natif reste possible ultérieurement |
| **Q3** | Chaque collaborateur interne doit **pouvoir** être relié à une identité canonique indépendante de WordPress |
| **Q4** | Les paiements sont dans la cible. Aucun prestataire choisi. **Horizon ne stockera aucune donnée bancaire sensible** |
| **Q5** | Un prestataire n'accède qu'aux données **strictement nécessaires à la prestation qui lui est affectée** |
| **Q6** | Dimensionnement **[H]** : quelques milliers de demandes/mois, quelques centaines de missions/jour, quelques dizaines de connexions simultanées |
| **Q7** | TypeScript/Deno acceptable, **usage différé et limité**. Aucune réécriture du domaine PHP |
| **Q1** | **Hébergement, cron et proxy non déterminés** — reste ouverte (§22) |

**Ce que Q6 permet d'écarter, chiffres à l'appui** : quelques milliers d'événements quotidiens, soit quelques événements par minute en moyenne. PostgreSQL absorbe cela sans difficulté. **Aucun bus externe, aucune file managée, aucun microservice n'est justifié.**

### 3.3 Principes transversaux du corpus

Formulés dans plusieurs ADR, ils gouvernent l'ensemble :

| Principe | Énoncé | Source |
|---|---|---|
| **Refus par défaut** | Aucune policy = aucun accès. L'absence de policy n'est jamais un défaut d'ouverture | ADR-004 §13 |
| **Chemin le moins privilégié** | Lecture filtrée → RLS. Écriture sans invariant → RLS. Commande → fonction sous JWT. Élévation seulement si nécessaire | ADR-005 §3, **A15** |
| **Un contrat non documenté n'est pas un contrat** | Un point d'extension livré sans documentation ni test est une dette | ADR-006 §13 |
| **La règle prime sur la liste** | Une action nouvelle est qualifiée par les critères, sans attendre une mise à jour d'inventaire | ADR-011 §2 |
| **Aucune étape n'absorbe son propre échec** | L'absence d'exécution d'un test est un échec, jamais une omission | ADR-015 §2 |
| **Une dégradation ferme, jamais n'ouvre** | Une dépendance indisponible restreint, elle n'élargit pas | ADR-013 §6.1, **CT2** |
| **Une procédure jamais exécutée est une hypothèse** | Ce n'est pas une capacité tant qu'elle n'a pas été éprouvée | ADR-013 §11, **CT3** |

---

## 4. Architecture hybride

*Source : ADR-001 §5 à §8*

### 4.1 Options étudiées et décision

| Option | Description | Verdict |
|---|---|---|
| **A** | WordPress runtime unique | **Écartée** — viole P3 frontalement |
| **B** | WordPress + Supabase, sans changement du modèle d'accès | **Écartée** — absence de décision, conduit à A |
| **C** | Accès direct Supabase, WordPress réduit au vitrine | **Écartée** — viole P1, P2, Q7 |
| **D** | **Architecture hybride incrémentale** | **RETENUE** |
| **E** | Service applicatif séparé | **Écartée** — viole P7, P8 |
| **F** | Edge Functions comme runtime principal | **Écartée** — viole P1, P2, Q7 |

**Motif du choix** : l'option D est la seule à satisfaire simultanément P1, P2, P3, P6, P7 et P8, et **la seule dont chaque étape est individuellement réversible**.

### 4.2 Les trois mouvements

1. **Supabase Auth devient la source d'identité canonique** de toutes les populations. WordPress reste le front de connexion du personnel interne (Q3).
2. **RLS devient la frontière de sécurité de référence pour tout accès non-WordPress.** Chaque policy constitue une nouvelle surface d'autorisation qui doit être testée indépendamment.
3. **Un second runtime (Edge Functions) est prévu mais différé**, introduit uniquement lorsqu'un critère objectif est rempli (§4.5).

### 4.3 Répartition des responsabilités — logique métier

*Source : ADR-001 §8.1*

| Couche | Détient | Ne détient jamais |
|---|---|---|
| **PostgreSQL / Supabase** | Intégrité référentielle, contraintes, unicité, **transitions interdites**, émission **transactionnelle** des événements, RLS | Orchestration, règles composites, décisions produit, communication |
| **Domaine PHP / runtime applicatif** | **Orchestration des cas d'usage**, règles métier complexes, composition des messages | Le rôle de frontière de sécurité pour les consommateurs externes |
| **RLS** | **Visibilité et droits élémentaires par ligne** | Toute règle métier composite, tout enchaînement |

**Sur la machine à états** : la base connaît la liste des transitions illégales et les refuse — c'est une contrainte d'intégrité. **Elle ne connaît ni les conditions métier d'une transition légitime, ni ses effets.** PHP conserve l'orchestration.

### 4.4 Surfaces d'accès aux données

*Source : ADR-001 §8.2 — **PostgREST n'est pas l'API universelle***

| Type d'opération | Voie |
|---|---|
| **Lectures simples** filtrées par ligne | PostgREST + RLS |
| **Écritures simples sans règle complexe** | PostgREST + RLS, **au cas par cas** |
| **Commandes métier** (transitions, affectations, envois) | Runtime serveur : PHP si le consommateur est WordPress, Edge Function sinon |
| **Opérations administratives sensibles** | Back-office WordPress ou API privée |

### 4.5 Critères d'introduction des Edge Functions

*Source : ADR-001 §15 — **l'introduction n'est pas une échéance mais une conséquence***

| # | Critère | Premier module concerné |
|---|---|---|
| **C1** | Un consommateur **non-WordPress** doit effectuer une écriture soumise à une règle métier | Application Chauffeur |
| **C2** | Un tiers doit appeler Horizon en **entrant**, authentifié par signature | Webhooks paiement, WhatsApp, Brevo |
| **C3** | Un agent IA doit exécuter un outil | Sprint IA |
| **C4** | Un traitement planifié doit s'exécuter **indépendamment du trafic WordPress** | Notification de mission à échéance |

> **Attention — collision de symboles.** Ces critères **C1 à C4** n'ont aucun rapport avec les critères de sensibilité **C1 à C6** de l'ADR-011 §2 (§13.1). Voir §22, anomalie 3.

**Non-critères explicites** : « le CRM en a besoin » · « il faut préparer l'avenir » · « c'est plus moderne » · « une lecture serait plus pratique ».

### 4.6 Maîtrise de la duplication des règles

*Source : ADR-001 §8.4*

**Réponse : par séparation des agrégats**, complétée par un recensement explicite des invariants transversaux (**T1 à T6**, §12.5).

- Le **cycle commercial** de la demande reste **intégralement en PHP**, piloté par le personnel interne.
- Le **cycle opérationnel** de la mission relève d'un runtime externe, piloté par le chauffeur.
- **Leurs interactions passent exclusivement par des commandes ou des événements explicites**, jamais par écriture croisée directe.

### 4.7 Périmètre des Edge Functions

*Source : ADR-001 §8.5*

**Elles peuvent assurer** : vérification du JWT · autorisation de la commande · validation d'entrée · idempotence · orchestration technique · appel à des fonctions transactionnelles · réception de webhooks signés · émission et corrélation des événements · passerelle des outils IA.

**Elles ne doivent jamais devenir** : une réécriture du domaine commercial PHP · un second back-office · un lieu de duplication de la logique métier · une API générique donnant accès au `service_role`.

### 4.8 Frontières de confiance

*Source : ADR-001 §10*

| # | Frontière | Statut | Contrôle |
|---|---|---|---|
| **B1** | Navigateur public ↔ WordPress | non fiable | Validation serveur, nonce, rate-limit, CORS **[F]** |
| **B2** | WordPress ↔ Supabase | fiable | `service_role`, secret confiné au serveur **[F]** |
| **B3** | PWA / application ↔ Supabase | **non fiable** | JWT + **RLS = la frontière** |
| **B4** | Edge Function ↔ Supabase | fiable | Runtime maîtrisé, secrets côté plateforme |
| **B5** | Agent IA ↔ passerelle d'outils | **non fiable** | Liste d'autorisation, périmètre, audit |
| **B6** | Prestataire de paiement ↔ Horizon | **non fiable** | Vérification de signature ; aucune donnée bancaire stockée |

---

## 5. Runtime

*Sources : ADR-001 §8.3, ADR-008*

### 5.1 Périmètre de chaque runtime

| Runtime | Détient | N'est jamais |
|---|---|---|
| **PostgreSQL** | Intégrité · RLS · **émission transactionnelle** (**E1**) · agrégats et comptages · maintenance purement base | Orchestration · appel externe · décision métier |
| **WordPress / PHP** | Site public · formulaire · back-office · **orchestration du domaine commercial** · `service_role` | Backend mobile · frontière de sécurité externe · **ordonnanceur fiable pour le temps-sensible** |
| **Edge Functions** | Commandes des consommateurs externes · webhooks signés · passerelle IA · planification indépendante du trafic | Second exemplaire du domaine commercial |
| **Workers** | **Livraison des événements** · traitement de la file de sortie | Toute décision métier |

**Le worker n'est pas un runtime : c'est un rôle**, hébergeable dans WordPress ou dans une Edge Function selon le handler *(ADR-008 §3)*.

### 5.2 Workers sans état — **E7**

*Source : ADR-008 §3.1*

Un worker ne conserve jamais : état métier · session durable · mémoire locale de reprise · progression implicite. **Toute reprise après incident repose exclusivement sur PostgreSQL** : réservation atomique · journal d'événements · tables de livraison.

**Conséquence** : tout worker peut être arrêté, remplacé, dupliqué ou déplacé sans perte de cohérence.

### 5.3 Où vit le worker d'événements — Q-V tranchée

*Source : ADR-008 §4*

**Le rôle de worker est assigné par handler, non fixé globalement.**

| Phase | Hôte | Justification |
|---|---|---|
| **1 — maintenant** | **WordPress** **[F]** | Aucun critère d'élévation rempli |
| **2 — Application Chauffeur** | **Edge Function planifiée** pour les handlers temps-sensibles | Critère C4 rempli |
| Durable | **Les deux coexistent** | — |

**Ce qui rend la migration sûre** : la réservation atomique déjà en place **[F]** garantit qu'un même élément ne peut être traité deux fois, **même si deux workers tournent simultanément**.

### 5.4 Garanties indépendantes du runtime — **E10**

*Source : ADR-008 §4.3*

Réservation atomique · idempotence · audit · corrélation · réessais · réconciliation · observabilité sont **indépendantes du runtime d'exécution**.

> **Le déplacement d'un handler entre WordPress et une Edge Function ne modifie jamais son comportement métier. Seul change le lieu d'exécution.**

### 5.5 Planification

*Source : ADR-008 §5*

| Option | Verdict |
|---|---|
| **WP-Cron seul** | **Insuffisant** — dépend du trafic **[F]** |
| **Cron système + désactivation de WP-Cron** | **Retenu, étape 1 — sous réserve de Q1** |
| **`pg_cron`** | **Retenu pour la maintenance purement base** |
| **Edge Function planifiée** | **Retenu dès que C4 est rempli** |
| Worker permanent dédié | **Écarté** — surdimensionné à Q6 |
| File externe managée | **Écarté** — P7 |

**Frontière stricte de `pg_cron`** — **E6** *(ADR-008 §5.1)* : réservé aux traitements purement base. Il ne planifie **jamais** une commande métier, un appel HTTP, un réveil de runtime ou une orchestration.

**Repli si Q1 est défavorable** *(ADR-008 §6)* : **une fonction planifiée côté Supabase peut réveiller WordPress**, en inversant le sens de l'appel. **Q1 ne bloque donc plus l'Application Chauffeur** — elle détermine seulement laquelle des deux voies est retenue.

### 5.6 Commandes longues

*Source : ADR-008 §7*

> **Aucune commande longue n'est synchrone.** Une commande dont le traitement peut dépasser le budget de la requête est scindée : la commande **accepte**, émet un événement, et le handler fait le travail.

### 5.7 Montée en charge — quatre leviers avant tout changement d'architecture

*Source : ADR-008 §8*

Plafond mesuré : **240 éléments par heure** (20 × 12) **[F]**.

1. augmenter la taille du lot ;
2. augmenter la fréquence ;
3. **paralléliser les workers** — sûr grâce à la réservation atomique **[F]** et à **E7** ;
4. changer de runtime d'exécution.

---

## 6. Authentification

*Source : ADR-003*

### 6.1 Frontière avec l'autorisation

> **Aucun mécanisme d'authentification n'accorde de droit.**

```
preuve d'authentification → ACTEUR → [contexte, rôles, organisation, affectation] → décision
```

**Critère de distinction** : une revendication technique décrit *comment la session a été établie* ; une autorisation métier décrit *ce que la personne a le droit de faire dans Horizon*. **La première peut vivre dans le jeton ; la seconde doit être relue en base.**

### 6.2 Décision par population

| Population | Mécanisme | Points saillants |
|---|---|---|
| **Administrateurs et collaborateurs** | **WordPress Auth maintenu**, avec résolution vers l'acteur canonique | **MFA obligatoire** avant l'extension importante du CRM (§6.4) |
| **Clients invités** | **Supabase Auth, lien magique, sur invitation uniquement** | Le mécanisme doit **empêcher toute création implicite de compte** |
| **Chauffeurs** | **Mot de passe + session renouvelable**, PWA installable — *choix initial explicitement réévaluable* | Jeton de renouvellement durable et rotatif ; jeton d'accès **de durée courte** |
| **Membres partenaires** | **Supabase Auth, lien magique**, invitation nominative par organisation | **Jamais de boîte partagée** ; **jamais d'administrateur global Horizon** |
| **Runtimes de confiance** | Secret machine, `service_role` confiné | Un secret par runtime et par environnement |
| **Webhooks** | **Signature du fournisseur** | Jamais par IP source, jamais par obscurité d'URL |
| **Agents IA** | Secret propre, **auprès de la passerelle seulement** | **Jamais de compte humain** (**I1**) |

**Trois mécanismes à ne pas confondre pour le client** *(ADR-003 §6.2)* : invitation administrative *(crée l'acteur)* · connexion par lien magique *(ouvre une session, ne crée jamais rien)* · inscription autonome *(non ouverte au démarrage)*.

### 6.3 Révocation — trois mécanismes distincts

*Source : ADR-003 §8.1*

| Mécanisme | Effet | N'assure **pas** |
|---|---|---|
| **1. Désactivation de l'acteur** | Retrait immédiat des droits Horizon **si chaque surface résout l'acteur** | L'invalidation du jeton déjà émis |
| **2. Révocation des jetons de renouvellement** | Empêche le renouvellement | L'invalidation du jeton d'accès en cours |
| **3. Expiration du jeton d'accès** | Met fin à la validité cryptographique | Rien avant l'échéance |

> **Une surface qui valide seulement la signature du JWT sans résoudre l'état actuel de l'acteur n'assure pas la révocation immédiate des droits Horizon.** (**A9**)

### 6.4 Multifacteur interne

*Source : ADR-003 §6.5, ADR-011 §3 (Q-K)*

**Obligatoire pour les comptes WordPress administrateurs et pour tout collaborateur disposant d'un accès Horizon, avant l'extension fonctionnelle importante du CRM.**

**Motif** : le serveur WordPress détient `service_role` **[F]** ; la compromission d'un compte privilégié expose l'ensemble des données — **et ce risque existe déjà en production**.

### 6.5 Rotation du `service_role` — protocole en dix étapes

*Source : ADR-003 §9.1*

1. Préparation et test complet en staging · 2. Génération ou rotation de la clé **[H]** · 3. Mise à jour sécurisée du secret · **4. Prise en compte effective par le runtime** *(dépend de Q1)* · **5. Redémarrage ou purge des caches** *(dépend de Q1)* · 6. Contrôle de lecture et d'écriture · 7. Contrôle des files et tâches planifiées · 8. Révocation de l'ancien secret · 9. Surveillance après bascule · 10. Consignation.

> **Une rotation improvisée pendant un incident est une seconde panne.**

### 6.6 Sécurité des PWA

*Source : ADR-003 §13*

**Principes fermes** : aucun stockage navigateur n'est protégé contre une compromission JavaScript de même origine · aucune donnée sensible hors ligne sans durée de vie ni effacement · aucune dépendance tierce non maîtrisée · politique de sécurité de contenu stricte · jeton d'accès court · rotation des jetons de renouvellement · nettoyage complet à la déconnexion · **les écritures hors ligne sont toujours réautorisées côté serveur à la synchronisation**.

**Q-J reste ouverte** : SDK Supabase côté PWA *(jeton lisible par le script)* ou mandataire de même origine avec cookie `HttpOnly` *(jeton inaccessible au script, mais serveur supplémentaire)* — **à trancher par prototype comparatif**, pas par préférence.

---

## 7. Autorisation

*Sources : ADR-004, Cartographie des flux d'autorisation*

### 7.1 Principe fondateur

État de départ : RLS active sur les 10 tables, **sans aucune policy**, WordPress accédant par `service_role` **[F]**.

> **Le refus par défaut est déjà acquis.** Cette ADR ne consiste pas à sécuriser un système ouvert, mais à **ouvrir de façon contrôlée un système fermé.**

### 7.2 Les quatre familles de chemins d'accès

*Source : Cartographie §1*

1. accès par `service_role` (WordPress, workers) ;
2. accès par JWT soumis à RLS (client, chauffeur, partenaire) ;
3. accès par passerelle à rôle restreint (IA) ;
4. accès anonyme borné (formulaire public, reçu).

> **Toute surface future doit se rattacher à l'une d'elles. Une cinquième famille serait un signal d'alerte architectural.**

**Point de rigueur** *(Cartographie §1, correction 1)* : deux familles échappent à RLS, **mais pour la même raison technique** — elles écrivent avec `service_role`. **L'absence d'acteur n'a aucun effet sur RLS** ; elle explique seulement l'attribution `NULL`.

### 7.3 La chaîne d'autorisation en douze étapes

*Source : Cartographie §2*

```
① identité → ② authentification → ③ ACTEUR CANONIQUE résolu → ④ état actif ?
→ ⑤ CONTEXTE (vérifié serveur) → ⑥ RÔLES relus en base ∩ contexte
→ ⑦ ORGANISATION → ⑧ AFFECTATION → ⑨ type d'opération
→ ⑩ QUALIFICATION par le runtime → ⑪ AUDIT → ⑫ ÉVÉNEMENT
```

**Étapes ③ ④ ⑤ ⑩ ⑪ sont obligatoires sur toute surface authentifiée.**

### 7.4 Les trois axes orthogonaux

*Source : ADR-002 §3 — **le point le plus important du modèle d'identité***

**Rôle**, **organisation** et **affectation** sont **trois axes d'autorisation orthogonaux**. Les confondre est le défaut qui rendrait Q5 impossible à satisfaire.

> Un partenaire possède un rôle, appartient à une organisation, mais **ne voit une mission que parce qu'une affectation le lui donne**. **Le rôle seul ne doit ouvrir aucune ligne de données** (**A2**, **A3**, **A4**).

### 7.5 Décisions tranchées

| Question | Décision | Source |
|---|---|---|
| **Q-H** — L'affectation vise l'organisation ou la personne ? | **L'organisation**, avec **désignation nominative optionnelle** | ADR-004 §3.1 |
| **Q-I** — Le contexte est-il une dimension explicite ? | **Oui.** Droits effectifs = rôles relus en base ∩ rôles admis dans le contexte | ADR-004 §3.2 |
| **Q-B** — Fin d'accès d'un partenaire | Trois états : **actif** → **grâce** *(lecture seule)* → **clos** *(aucun accès)* | ADR-004 §3.3 |
| **Q-L** — Seuil de désignation nominative | **Dès le deuxième membre actif** *(recommandation, SEC1)* | ADR-011 §3 |
| **Q-M** — Durées actif / grâce / clos | Grâce = **7 jours** *(recommandation, SEC1)* | ADR-011 §3 |

**Sur le contexte** — règle impérative :

> « Le contexte déclaré dans le jeton constitue une **intention d'accès**. Il doit être vérifié côté serveur. »

Le contexte doit toujours : **restreindre** · ne jamais élargir · être **refusé s'il est absent** sur une surface qui l'exige · être **refusé s'il est incompatible** · être **relu avant toute commande métier** (**A1**).

**Le mécanisme physique du contexte n'est pas décidé** — cinq options restent à comparer, **Q-O ouverte**.

### 7.6 Séparation des données commerciales et opérationnelles

*Source : ADR-004 §4 — **la décision la plus contraignante pour le modèle de données***

| Nature | Chauffeur | Partenaire | Client | Interne |
|---|---|---|---|---|
| **Opérationnelle** | **oui**, si affecté | **oui**, si affecté | ses propres données | oui |
| **Commerciale** | **non** (**⛔1**) | **non** (**⛔1**) | strictement le sien | oui |
| **Identifiante** | **minimum nécessaire**, borné dans le temps | idem | lui-même | oui |

> **RLS agit au niveau de la ligne, pas de la colonne.** Protéger une colonne commerciale par une policy est impossible. **Les données commerciales et opérationnelles doivent donc être séparables au niveau des relations.**

S'en remettre au code applicatif pour ne pas sélectionner une colonne **n'est pas une frontière de sécurité**.

### 7.7 Relations dédiées — ce qu'elles sont et ne sont pas

*Source : ADR-004 §5*

**Mécanisme retenu pour la portée colonne** : relations dédiées par consommateur, plutôt que privilèges de colonne *(invisibles à la lecture du schéma, dérivant silencieusement, non testables naturellement)*.

> **« Une relation dédiée réduit le risque d'exposition accidentelle et rend le contrat de données plus lisible. Elle ne constitue pas, à elle seule, une frontière de sécurité. »** (**A8**)

**La frontière reste portée par** : RLS · les fonctions appelées par les policies · les privilèges explicites · l'autorisation des commandes métier · la séparation effective des données.

**Sept vérifications requises** avant toute mise en service, et **Q-N reste ouverte et bloquante**.

### 7.8 Commandes métier contre écritures directes

*Source : ADR-004 §8*

> **Règle de tri : si une écriture engage un invariant qui dépasse la ligne écrite, c'est une commande. Sinon, l'écriture directe est acceptable.**

**Idempotence obligatoire** *(ADR-004 §8 bis, **A10**)* : toute commande métier exposée à un consommateur externe doit tolérer les répétitions. **L'idempotence n'est pas une responsabilité du client seul : elle est garantie côté serveur.**

### 7.9 Acteurs de service et `service_role`

*Source : ADR-004 §10 — **point sans ambiguïté***

`service_role` **contourne RLS par construction** **[F]**. Les acteurs de service sont **hors du système de policies**. **RLS ne les protège pas et ne les protégera jamais.**

Leur encadrement repose sur trois autres leviers : **confinement** (**A7**) · **attribution** — un acteur de service par usage (**I14**, **A13**) · **rotation** (ADR-003 §9.1, **SEC4**).

> **Énoncer clairement cette limite vaut mieux que de laisser croire que RLS couvre tout.**

### 7.10 Accès des agents IA

*Source : ADR-004 §11*

| | Mécanisme | Portée du contrôle |
|---|---|---|
| A | La passerelle détient `service_role` et applique le périmètre **dans son code** | Point de défaillance unique |
| **B** | La passerelle obtient un **rôle de base restreint par classe d'agent**, soumis aux policies | **RLS s'applique aussi à l'IA** |

**Option B retenue.** En A, l'agent est limité par du code ; en B, il est limité par la base — **la même frontière que tous les autres consommateurs externes**.

### 7.11 Actions sensibles

*Source : ADR-004 §12 et §12 bis, ADR-011 §2*

> **« La qualification d'une action comme sensible appartient exclusivement au runtime et aux règles d'autorisation Horizon. Un agent IA, un client, un partenaire ou tout autre consommateur ne peut jamais déclarer lui-même qu'une action est non sensible. »** (**A6**)

**La classification porte sur l'effet réel de l'action, non sur le nom de l'outil invoqué.** Deux outils produisant le même effet reçoivent la même qualification.

**Six critères de sensibilité** *(ADR-011 §2)* — voir §13.1.

**Règle absolue** : ces actions exigent un **acteur humain résolu** (**A5**, **⛔3**). **Aucun repli d'attribution n'est admis** (**I16**).

### 7.12 Refus par défaut — cinq règles

*Source : ADR-004 §13*

1. Toute nouvelle table active RLS **dans la migration qui la crée**.
2. **Aucune policy = aucun accès.**
3. Les droits sont **additifs et explicites** ; aucune policy formulée en négatif (**A11**).
4. Une table livrée sans jeu de policies explicite est une **erreur de déploiement** (**A12**).
5. Toute policy est introduite **avec ses tests**, jamais avant.

---

## 8. API

*Source : ADR-005*

### 8.1 Les huit surfaces

| # | Surface | Auth | Privilège | Famille (§7.2) |
|---|---|---|---|---|
| 1 | Routes publiques WordPress | aucune | `service_role` via PHP | anonyme borné |
| 2 | Lectures authentifiées PostgREST | JWT | aucun — RLS | JWT sous RLS |
| 3 | Commandes WordPress internes | Session WP | `service_role` via PHP | privilégié |
| 4 | **Edge Functions sous JWT utilisateur** | JWT | **aucun — RLS** | JWT sous RLS — **défaut** |
| 5 | **Edge Functions privilégiées** | JWT ou signature | `service_role` **justifié** | privilégié — **exception** |
| 6 | API privées administratives | Session WP + MFA | `service_role` | privilégié |
| 7 | Webhooks entrants | Signature | `service_role` | privilégié |
| 8 | Passerelle d'outils IA | Secret d'agent | **rôle restreint** | passerelle |

### 8.2 Sort des cinq routes existantes

*Source : ADR-005 §7*

| Route | Décision | Catégorie de changement |
|---|---|---|
| `POST /enquiries` | **Maintenue, durcie** | **A** — compatible |
| `GET /form-token` | **Maintenue, alignée** sur les autres | **A** |
| `GET /health` | **Réduite en public** — liveness minimale seule | **A** |
| `POST /whatsapp-opened` | **Liée à une preuve de possession** | **B** — incompatible |
| `GET /enquiries/{ref}/receipt` | **Mode d'accès refondu** — jeton non devinable | **B** |

**Point de rigueur acté** *(ADR-005 §7a et condition 5)* : le commentaire du code annonce « aucun oracle d'existence », alors que **le code renvoie 404 pour une référence inconnue et 200 sinon** **[F]**. **C'est un oracle d'existence, et le commentaire est contredit par son propre code.** La cible supprime l'oracle.

**Décision intermédiaire sur le débit** : **aucun nouveau contrôle dépendant de l'IP n'est introduit tant que Q1 n'est pas résolue.**

### 8.3 Traçabilité des lectures

*Source : ADR-005 §5.1, ADR-012 §4*

> **« Toute lecture authentifiée doit être attribuable techniquement. Les lectures sensibles produisent en plus un audit métier explicite. »**

La trace technique est **agrégeable ou échantillonnable** ; l'audit métier de lecture **jamais**.

### 8.4 Pagination et limites — cinq règles

*Source : ADR-005 §14*

1. **Toute liste a une limite par défaut et une limite maximale.** Aucune exception.
2. **Aucun agrégat calculé côté application** — les comptages sont faits par la base.
3. Pagination par curseur préférée sur les ensembles appelés à croître.
4. Filtrage et tri sur un **ensemble déclaré** de champs.
5. **Toute lecture sans limite est un défaut.**

### 8.5 Erreurs

*Source : ADR-005 §16*

**Ne jamais exposer** : détail SQL · secret · structure interne · **existence d'une ressource lorsque cela facilite l'énumération**.

**Une répétition idempotente est un succès, jamais une erreur** — sinon le fournisseur rejouera indéfiniment.

### 8.6 API privées administratives

*Source : ADR-005 §10*

> **Une origine HTTP et une politique CORS ne constituent pas une frontière d'autorisation.**

**Quatre notions à ne pas confondre** : publiquement routable · authentifié · autorisé · restreint au niveau réseau *(défense complémentaire, jamais principale)*.

> **Certaines opérations administratives resteront accessibles depuis Internet pour les utilisateurs autorisés, sans être « publiques » pour autant.**

---

## 9. Contrats

*Sources : ADR-005 §3.1, §6, §13 · ADR-006 §13 · ADR-014 §8*

### 9.1 Contrats partagés et contrats spécialisés

> **« Les contrats métier peuvent être partagés lorsqu'ils portent exactement la même sémantique, les mêmes garanties et le même niveau de confidentialité. Les projections de données et les commandes doivent être spécialisées dès que les populations, les droits, la confidentialité ou les règles d'autorisation diffèrent. »**

| Niveau | Partage | Exemples |
|---|---|---|
| **Types de valeur** | **Partagés** | Identifiant · adresse normalisée · statut · période · pagination · erreur standard |
| **Projections** | **Spécialisées** | Vue CRM · vue chauffeur · vue partenaire · vue client |
| **Commandes** | **Spécialisées** par domaine et niveau d'autorisation | Transition commerciale ≠ transition opérationnelle |

> **« La réutilisation d'un schéma ne doit jamais conduire à exposer une donnée supplémentaire simplement parce qu'un autre consommateur en a besoin. »**

### 9.2 Les quatre contrats d'intégration — **G8**

*Source : ADR-006 §8*

| | **Commande** | **Requête** | **Webhook** | **Événement métier** |
|---|---|---|---|---|
| Nature | Intention | Question | Notification entrante | **Fait accompli** |
| Temps | À l'impératif | Au présent | Passé, chez le tiers | **Au passé, immuable** |
| Peut échouer | **Oui** | Oui | *(déjà survenu)* | **Non** |
| Destinataires | **Un** | Un | Nous | **Zéro à n** |
| Couplage | Fort, assumé | Fort, assumé | Imposé par le tiers | **Faible** |
| Idempotence | **Clé obligatoire** | Naturelle | Identifiant fournisseur | Identifiant d'événement |

**La commande *fait faire* · la requête *interroge* · le webhook *informe depuis l'extérieur* · l'événement *constate*. Aucun ne remplace l'autre.**

### 9.3 Versionnement

*Source : ADR-005 §13, ADR-014 §2*

**Cinq portées de version, aucune version globale** *(ADR-014 §2)* : jalon de dépôt · plugin · thème · contrat · Edge Function.

**Trois catégories de changement** *(ADR-005 §13.1)* :

| Cat. | Nature | Nouvelle version ? |
|---|---|---|
| **A** | Durcissement compatible | **Non** |
| **B** | Changement incompatible | **Oui** — nouvelle version ou double fonctionnement |
| **C** | Correction urgente de sécurité | **Rupture contrôlée admise**, documentée et annoncée |

### 9.4 Compatibilité ascendante — matrice

*Source : ADR-014 §8*

| Type de contrat | Compatible | **Incompatible** |
|---|---|---|
| Route publique | Durcissement de catégorie A | Catégories B et C |
| Interface PHP | Méthode optionnelle, entrée élargie | Retrait, entrée restreinte, sortie élargie, sens changé |
| Hook | Charge utile enrichie | Retrait, changement de forme |
| Façade JS | Méthode ajoutée | Signature modifiée |
| Événement | Champ ajouté | Champ retiré, sens changé |
| Schéma | Colonne nullable, table, index | Retrait, renommage, resserrement |
| Projection dédiée | — | **Toute évolution ⇒ nouvelle relation** |

> **Le cas le plus dangereux traverse toutes les lignes : un changement de sens à forme identique.** Aucun contrôle automatique ne le détecte. Il exige une **migration de contrat écrite**.

### 9.5 Idempotence

*Source : ADR-005 §15, ADR-007 §11*

**Portée : (principal d'idempotence, type de commande, clé)**

> **« La clé d'idempotence est créée par l'initiateur fiable de l'intention et reste stable lors de tous les rejeux de cette même intention. »**

**Huit exigences** : empreinte du contenu conservée · même clé + même contenu → résultat rejoué · même clé + contenu différent → **conflit explicite** · statut de traitement stocké · **gestion des commandes en cours** · résultat rejouable sans reproduire les effets · durée de rétention par type *(Q-P)* · **aucune confiance dans la seule bonne conduite du client**.

**Trois niveaux coexistent** *(ADR-007 §11)* : commande *(clé)* · livraison *(événement, handler)* · webhook *(identifiant fournisseur)*.

**Stockage : base, jamais transients WordPress** — durabilité et visibilité inter-runtimes.

---

## 10. Extensibilité

*Source : ADR-006*

### 10.1 Objectifs et non-objectifs

**Objectifs** : permettre à un second consommateur d'exister sans modifier le cœur · empêcher le couplage direct entre modules · garantir qu'aucune extension ne puisse affaiblir la sécurité · rendre les contrats testables et versionnés.

**Non-objectifs explicites** : Horizon n'est pas un framework générique · pas de catalogue de hooks « au cas où » · pas de place de marché · pas de chargement dynamique · **aucune exécution de code fourni par un utilisateur**.

> **Règle de création : un point d'extension n'est introduit que lorsqu'un second consommateur réel ou planifié le justifie.**

### 10.2 Les trois niveaux de contrat

| Niveau | Mécanisme | Usage | Garantie | Portée |
|---|---|---|---|---|
| **1 — Contrat de domaine** | **Interface PHP typée** | Substitution d'un comportement critique | Typage, tests de contrat | Intra-PHP |
| **2 — Événement métier** | Journal + handlers | Réaction à un fait, découplage | Durable, rejouable, idempotent | **Inter-runtime** |
| **3 — Hook WordPress** | `apply_filters` / `do_action` | Adaptation locale **non critique** | **Aucune** | Intra-WordPress |

**Règle de choix** : si le contrat est critique, c'est une interface. S'il s'agit de réagir à un fait, c'est un événement. S'il s'agit d'un ajustement local sans conséquence, c'est un hook.

### 10.3 Principe transversal — **G7**

> **« Une extension peut observer, enrichir, proposer ou transformer une représentation autorisée. Elle ne modifie jamais directement un agrégat métier critique en dehors des commandes prévues par le domaine. »**

### 10.4 Les dix zones interdites aux hooks — **G5**

*Source : ADR-006 §7.1 — **s'appliquent aux trois niveaux de hook, sans exception***

1. Résolution de l'acteur · 2. Décision d'autorisation · 3. Construction des prédicats de policy · 4. Validation serveur · **5. Écriture d'audit** · **6. Qualification de sensibilité** · 7. Vérification d'idempotence · 8. Vérification de signature de webhook · 9. Sélection d'un secret ou d'une configuration privilégiée · **10. Émission d'un événement métier**.

**Deux règles impératives** : **la valeur de retour d'un filtre est une entrée non fiable** (**S3**) · **toute dépendance d'ordre d'exécution est explicite et testée**.

### 10.5 Cycle de vie des interfaces — **G1**, **G2**, **G3**

| Statut | Garantie |
|---|---|
| **Experimental** | Aucune — interne uniquement |
| **Stable** | Signature figée ; seule une extension compatible est admise |
| **Deprecated** | Fonctionne encore ; remplacement désigné ; date de retrait annoncée |
| **Removed** | N'existe plus |

**Conditions de passage à Stable** : au moins **deux implémentations réelles** · documentation écrite · **suite de tests de contrat** · signature inchangée depuis un cycle.

**Aucune interface nouvelle n'est créée par le corpus.** Les quatre existantes — `SupabaseGateway`, `CommunicationProvider`, `EmailProvider`, `ProviderResolver` — sont déclarées **Stable** **[F]**.

### 10.6 Classification des hooks — **G4**

| Niveau | Nom | Garantie | Documentation | Test |
|---|---|---|---|---|
| **1** | Hook interne | **Aucune** | Non | Non |
| **2** | Hook documenté | Compatibilité raisonnable | Oui | Recommandé |
| **3** | Hook contractuel | Versionné, compatible | **Obligatoire** | **Obligatoire** |

**Un hook non déclaré est de niveau 1 par défaut. La promotion vers le niveau 3 est une décision, jamais une conséquence de l'usage.**

### 10.7 Sécurité des extensions — règles S1 à S7

*Source : ADR-006 §11*

| # | Règle |
|---|---|
| **S1** | Aucune extension ne reçoit de secret |
| **S2** | Aucune extension ne reçoit `service_role` |
| **S3** | La sortie d'une extension est une **entrée non fiable** |
| **S4** | Une extension ne peut pas élever ses droits |
| **S5** | Aucun hook ne permet de neutraliser RLS, l'autorisation, l'audit ou la validation serveur |
| **S6** | Une extension ne peut pas modifier la qualification de sensibilité |
| **S7** | Une extension **ne modifie jamais un agrégat critique hors des commandes du domaine** |

**Point d'honnêteté** *(ADR-006 §11)* : dans WordPress, **il n'existe aucune isolation entre extensions**. Un plugin tiers partage le processus PHP et peut lire les constantes, donc `service_role` **[F]**. **Ajouter des hooks n'affaiblit donc pas la sécurité — la surface existe déjà, entièrement.** Le seul contrôle est la **gouvernance des plugins** (§13.6).

### 10.8 Gestion des erreurs par niveau

*Source : ADR-006 §12*

| Niveau | Comportement en cas d'échec |
|---|---|
| **1 — Interface** | L'échec fait échouer l'opération, **en refus fermé**, jamais silencieusement |
| **2 — Événement** | **L'écriture métier est déjà validée.** Le handler échoue seul, est réessayé. **Le fait n'est jamais perdu** |
| **3 — Hook** | Isolé, hors section transactionnelle. Une erreur ne doit ni annuler l'écriture, ni interrompre la chaîne |

---

## 11. Architecture événementielle

*Source : ADR-007*

### 11.1 Parti pris

**Généraliser un patron déjà validé en production, sans introduire de courtier de messages.** À l'échelle de Q6, PostgreSQL absorbe la charge sans difficulté ; un courtier externe serait un surdimensionnement (P7, P9).

**Options écartées** : appel direct entre modules · hook WordPress · courtier externe · **event sourcing** (§11.7).

### 11.2 Nature d'un événement

Un événement est un **fait accompli, immuable, au passé**. Il ne peut ni échouer, ni être annulé, ni être modifié. **Une erreur se corrige par un nouvel événement compensatoire, jamais par la réécriture du précédent.**

**Attributs conceptuels** : type et version · agrégat et identifiant · **rang dans l'agrégat** · charge utile minimale · date de survenance · **acteur** · **identifiant de corrélation** · **identifiant de causalité** · éventuel acteur approbateur.

**Règle de charge utile** : **minimale**. Elle est soumise **aux mêmes règles de confidentialité que les contrats API** (**⛔1**) — **sans exception de destinataire**.

### 11.3 Monotonie du journal — **E2**

| Opérations admises | Opérations interdites |
|---|---|
| **Ajout** · **Archivage** · **Purge selon politique documentée** | Modification du contenu · du type · du rang · **Suppression individuelle** |

**Purge et archivage portent sur des ensembles définis par une politique écrite, jamais sur un événement isolé.**

### 11.4 Émission transactionnelle

> **L'événement doit être écrit dans la même transaction que le changement d'état.** Or PostgREST ouvre une transaction par requête **[F]** : deux appels applicatifs successifs perdraient des événements en cas de panne intermédiaire.

| Type d'événement | Émission |
|---|---|
| **Événement d'état** — dérivable mécaniquement | **Déclencheur en base** |
| **Événement porteur de sens métier** | **Émis par la commande, dans la même transaction** |

> **On délègue à PostgreSQL la garantie d'atomicité, jamais la décision métier.**

**Frontière stricte des déclencheurs — E1** *(ADR-007 §5.1)* : un déclencheur ne peut produire qu'un événement **entièrement déterminé par les données déjà validées**. Interdits : appel HTTP · décision métier · consultation externe · choix fonctionnel · orchestration.

### 11.5 Handlers et livraison

**Un état de livraison par couple (événement, handler)** → **idempotence structurelle**. **Réservation atomique** avant traitement **[F]**.

**Isolation des handlers — E3** *(ADR-007 §6.1)* :

> **« Un handler ne déclenche jamais directement un autre handler. »**

La chaîne est : `commande → nouvel événement → nouveau handler`.

### 11.6 Garanties de livraison et ordre

> **Livraison au moins une fois. Jamais exactement une fois.** *(ADR-007 §7)*

**Corollaire non négociable : tout handler est idempotent.**

**Ordre** *(ADR-007 §8)* : garanti **au sein d'un même agrégat**, par un rang monotone. **Non garanti entre agrégats différents.** Tout handler doit tolérer un désordre inter-agrégats ; **aucune décision métier ne dépend d'un ordre global**.

### 11.7 Ce que le corpus ne fait pas

> **Horizon n'est pas événementiellement sourcé.** *(ADR-007 §12)*

Les tables d'état restent la **source de vérité de l'état**. Le journal est un **journal de faits**, pas l'état lui-même. **Aucune table de lecture dénormalisée** n'est créée tant qu'un besoin de performance réel n'est pas mesuré.

### 11.8 Réessais et file d'échecs

*Source : ADR-007 §10 — **correction d'un défaut constaté** **[F]***

| Classe | Exemples | Traitement |
|---|---|---|
| **Transitoire** | Expiration, indisponibilité, limitation de débit | Réessai à délai progressif |
| **Permanente** | Authentification refusée, charge utile invalide | **Échec immédiat, sans réessai** |
| **Ambiguë** | Réponse inattendue | Réessai borné, puis échec |

**Une entrée en échec définitif n'est jamais supprimée automatiquement : c'est une trace d'incident.**

### 11.9 Rejeu et réconciliation

> **Rejouer n'est pas ré-émettre.** On relivre l'événement existant ; on ne crée jamais un nouvel événement pour refaire quelque chose.

**Réconciliation en lecture seule — E4** *(ADR-007 §15.1)* : elle peut détecter, mesurer, signaler, alerter, produire un rapport. Elle ne peut **jamais** créer ou modifier un événement, corriger une ligne, **relivrer automatiquement**, ni changer un statut.

### 11.10 Synchronisation entre runtimes

Le journal vit dans PostgreSQL, lisible par tous les runtimes de confiance. **Aucun courtier, aucune file externe.**

> **Un consommateur externe ne lit jamais le journal d'événements.** Exposer le journal reviendrait à contourner la séparation commerciale / opérationnelle.

### 11.11 Événements par domaine

| Domaine | Événements |
|---|---|
| **Commercial** | `EnquiryCreated` · `EnquiryAssigned` · `EnquiryStatusChanged` |
| **Opérationnel** | `MissionCreated` · `MissionAssigned` · `DriverAcceptedMission` · `DriverArrived` · `PassengerOnBoard` · `MissionCompleted` |
| **Communication** | `CommunicationRequested` · `CommunicationSent` |
| **Paiement** | `PaymentRequested` · `PaymentReceived` |
| **Partenaires** | `PartnerBookingRequested` · événements d'affectation |
| **Conciergerie** | `ConciergeRequestCreated` |
| **IA** | `AIActionProposed` · `AIActionApproved` · `AIActionExecuted` |

**Pipeline unique pour l'IA — E5** *(ADR-007 §14.1)* : **aucun mécanisme spécifique n'est réservé aux événements IA.** Un chemin dédié serait un chemin moins éprouvé, donc plus faible.

---

## 12. Architecture d'exécution

*Source : ADR-008* — voir également §5 (Runtime), dont cette section est le complément d'exploitation.

### 12.1 Déploiement progressif

*Source : ADR-008 §11*

| Mécanisme | État |
|---|---|
| **Drapeaux de fonctionnalité** | La table existe avec trois valeurs, **mais aucun code ne la lit** **[F]** |
| **Coexistence de workers** | Sûre par réservation atomique **[F]** |
| **Déploiement des policies** | Table par table, chacune testée indépendamment |

**Pas de déploiement canari** : à cette échelle, **l'environnement de staging est le mécanisme de progressivité**.

### 12.2 Gouvernance des drapeaux — **E8**

> **« Un drapeau de fonctionnalité active ou désactive une fonctionnalité. Il ne décide jamais d'une autorisation, d'un rôle, d'une règle métier, d'une policy RLS ni d'un niveau de sécurité. »**

> **Un drapeau désactivé doit rendre une fonctionnalité absente, jamais « présente mais interdite ». Si la seule chose qui empêche un accès est un drapeau, l'autorisation est manquante.**

### 12.3 Séparation Production / Staging — **E9**

*Source : ADR-008 §12 et §12.1*

**Manque vérifié, risque d'exploitation réel** : `isProduction()` existe mais **n'est jamais appelé** ; `FCP_ENV` n'est lu que pour être affiché dans `/health` **[F]**. **Aucun garde-fou d'environnement ne protège les envois sortants.**

**Cinq exigences** : garde-fou d'environnement sur toute communication sortante · ordonnanceur et workers propres à chaque environnement · secrets distincts **[F]** · staging systématiquement avant production **[F]** · données de staging sans données personnelles réelles non nécessaires.

> **Avant tout déploiement : un environnement non Production ne doit jamais pouvoir envoyer une communication réelle.** C'est une exigence **vérifiable automatiquement** et un **critère obligatoire avant mise en production**.

### 12.4 Supervision de l'exécution

*Source : ADR-008 §9*

> **Un battement de l'ordonnanceur est obligatoire, et son absence doit alerter.**

**C'est le seul défaut qui ne produit aucune erreur** : si l'ordonnanceur s'arrête, rien n'échoue — la file grossit simplement, en silence.

### 12.5 Tolérance aux pannes

*Source : ADR-008 §10*

| Panne | Comportement attendu |
|---|---|
| **Supabase indisponible** | Le formulaire refuse proprement, **aucun lien WhatsApp fourni** **[F]** |
| **Fournisseur d'envoi indisponible** | Réessais ; **la demande n'est jamais perdue** **[F]** |
| **WordPress indisponible** | **Les consommateurs externes continuent de fonctionner** — propriété nouvelle de l'hybride |
| **Ordonnanceur arrêté** | Battement + alerte |
| **Edge Function en échec** | Refus explicite, jamais silencieux |
| **Worker interrompu** | Détection par âge, reprise, réconciliation |

---

## 13. Architecture IA

*Source : ADR-009*

### 13.1 Rôle exact des agents

> **Un agent est un assistant d'un opérateur humain, jamais un acteur autonome du métier.**

Il propose, rédige, analyse, résume. **Il ne décide pas.**

**Corollaire d'exploitation contraignant** : **aucun processus métier ne dépend de la disponibilité d'un agent.** Si tous les agents sont indisponibles, l'activité continue à l'identique, seulement moins assistée.

**Décision de positionnement** : **au démarrage, aucun agent ne converse directement avec un client.** Ouvrir la conversation directe est une **décision produit distincte** (Q-AC).

### 13.2 Niveaux d'autonomie

| Niveau | Peut | Ne peut pas |
|---|---|---|
| **N0 — Observateur** | Lire, analyser, résumer | Produire quoi que ce soit d'adressé à un tiers |
| **N1 — Rédacteur** | Produire un brouillon **interne** | Écrire en base métier, envoyer |
| **N2 — Proposeur** | Émettre une **proposition d'action** | Exécuter |
| **N3 — Exécutant sur approbation** | Exécuter **après approbation humaine** | Exécuter sans approbation |
| **N4 — Exécutant autonome** | Exécuter des actions **non sensibles** | Toute action sensible |

**N4 n'est pas ouvert au démarrage.** La progression se fait **niveau par niveau**, chaque palier étant une décision documentée.

| Agent | Niveau initial | Périmètre de données |
|---|---|---|
| **Concierge** | **N1** | Le plus restreint — **ne parle jamais au client** |
| Commercial | N2 | Demandes, historique commercial |
| Support | N2 | Demandes, communications |
| Assistant interne | N1–N2 | Données internes, agrégées de préférence |
| Marketing | N0–N1 | **Agrégats uniquement, aucune donnée personnelle** |
| Direction artistique | **N0** | **Aucune donnée métier** |
| Recherche & Développement | **N0** | Données anonymisées |

### 13.3 La passerelle d'outils — onze contrôles

*Source : ADR-009 §5 — **surface unique de l'IA***

1. authentifier l'agent · 2. résoudre l'**acteur IA** et sa **configuration versionnée** · 3. **vérifier le statut** · 4. vérifier l'outil dans la liste autorisée **de cette version** · 5. valider l'entrée · 6. **qualifier l'effet réel** · 7. contrôler la compatibilité avec le **niveau d'autonomie** · 8. appliquer quotas et débit · 9. exécuter **avec le rôle de base restreint** · 10. **auditer avec double attribution** · 11. émettre par le **pipeline commun** (**E5**).

> **La passerelle est le seul lieu où la capacité d'un agent est appliquée.** Sa complexité est un risque de sécurité direct.

### 13.4 Configuration et consignes

**Règle déterminante** : **une configuration est immuable ; toute modification crée une nouvelle version.**

Deux raisons : **reproductibilité de l'audit** · **retour arrière**. C'est la même propriété que le journal d'événements (**E2**) — **on ne réécrit pas ce sur quoi repose une explication.**

**Gouvernance des consignes — IA2** : les consignes système sont des **artefacts logiciels**, portant identifiant · version · **auteur** · **justification** · date d'activation · historique · statut.

> **A14 reste absolu** : un agent n'a **aucun accès à sa propre configuration**, ni en lecture ni en écriture (**⛔2**).

### 13.5 Outils

| Règle | Énoncé |
|---|---|
| **Granularité** | **Un outil = une intention.** Jamais d'outil générique |
| **Interdits absolus** | Requête SQL arbitraire · appel HTTP arbitraire · accès fichier arbitraire · exécution de code |
| Lecture / écriture | Un outil de lecture s'exécute ; **un outil d'écriture produit une proposition** |

> **Un seul outil générique viderait la liste d'autorisation de son sens.** La liste n'a de valeur que si chaque outil est étroit.

**Les outils comme contrats fonctionnels — IA3** : « Les outils constituent les véritables contrats fonctionnels. Le modèle ne fait que décider quand les appeler. » **Les tests de conformité ne dépendent d'aucun fournisseur et sont exécutables sans modèle.**

### 13.6 Mémoire

| Type | Statut |
|---|---|
| **Mémoire de tâche** | Autorisée, courte et bornée |
| **Mémoire de configuration** | Versionnée, immuable |
| **Mémoire d'apprentissage** | **Interdite** — aucun entraînement sur données clientes |

> **La mémoire ne contourne jamais l'autorisation. Ce qu'un agent conserve ne doit jamais excéder ce qu'il aurait le droit de lire à l'instant présent.**

### 13.7 Approbation humaine — trois règles

*Source : ADR-009 §9*

| Règle | Énoncé |
|---|---|
| **R1** | **On ne peut approuver que ce que l'on pourrait faire soi-même** — sans quoi l'IA deviendrait un chemin d'élévation de privilège |
| **R2** | **L'approbation est elle-même une action sensible** — acteur humain résolu, refus fermé sans liaison, audit |
| **R3** | **Une approbation expire** — non exécutée dans un délai borné, elle devient caduque |

> **Attention — collision de symboles.** Ces règles **R1 à R3** sont distinctes des règles d'alerte **R1 et R2** de l'ADR-012 §7 (§15.4). Voir §22, anomalie 2.

### 13.8 Sécurité — l'injection est contenue, jamais empêchée

*Source : ADR-009 §12*

Un agent lit des données qui peuvent contenir des instructions. **Ce risque ne peut pas être éliminé.**

**Ce que l'architecture garantit** : **même intégralement détourné, un agent ne peut pas dépasser sa liste d'outils, ni son niveau d'autonomie, ni les policies attachées à son rôle de base.**

**Ce qu'elle ne garantit pas** : qu'un agent détourné n'utilisera pas **mal** ses outils autorisés. **La réponse à ce résidu est l'approbation humaine, pas un contrôle technique.**

### 13.9 Indépendance vis-à-vis du fournisseur — **IA1**

> **« Un agent Horizon dépend d'un contrat de capacités, jamais d'un fournisseur particulier. »**

Le domaine métier ne connaît **aucun** fournisseur de modèle. **Changer de fournisseur ne modifie ni les outils, ni les autorisations, ni les niveaux d'autonomie, ni les événements métier.**

Cela transforme **Q-AA** : la question du fournisseur **cesse d'être une décision d'architecture** et devient un choix contractuel et réglementaire, révocable.

**Quotas — IA4** : le **quota métier** appartient au domaine ; le **quota technique** et le **coût fournisseur** restent confinés au runtime IA. **Le domaine Horizon ne manipule jamais de jetons, de crédits ni d'unités de facturation.**

### 13.10 Explicabilité — **IA5**

L'audit doit permettre de retrouver : configuration · version de consigne · version de modèle · outils autorisés · données d'entrée pertinentes · qualification · décision humaine · événements produits.

> **Objectif : comprendre pourquoi une proposition a été produite. Il n'est pas exigé d'obtenir exactement le même texte** — les modèles ne sont pas déterministes, et ce que l'on doit expliquer, c'est le raisonnement autorisé, pas la formulation.

---

## 14. Modèle de données

*Source : ADR-010*

### 14.1 Les quatre zones de confidentialité

**Décision fondatrice** : toute relation appartient à **une seule zone**, déclarée.

| Zone | Contenu | Lisible par |
|---|---|---|
| **Identifiante** | Nom, courriel, téléphone, adresses | Interne · l'intéressé |
| **Commerciale** | Catégorie, résumé, devis, prix, marge, notes internes, historique | **Interne uniquement** |
| **Opérationnelle** | Trajet, horaire, passagers, contraintes d'exécution | Interne · exécutant **affecté** |
| **Technique** | Journal, livraisons, idempotence, migrations | Runtimes de confiance |

**Les zones ne sont pas des silos — D1** *(ADR-010 §3.1)* :

> **« Ce qui est interdit n'est pas toute duplication, mais la traversée non maîtrisée d'une frontière de confidentialité. »**

**Toute duplication inter-zone — D2** doit être : **justifiée · minimale · explicitement modélisée · soumise à une rétention propre · protégée par les autorisations correspondant au nouvel usage**.

### 14.2 Les neuf agrégats

| Agrégat | Zone |
|---|---|
| **Acteur** *(acteur, attributions de rôle)* | Technique / identifiante |
| **Contact** *(fiche CRM)* | **Identifiante** |
| **Organisation** *(organisation, membres)* | Identifiante |
| **Demande** *(demande, détails, notes internes)* | **Commerciale** |
| **Mission** *(mission, affectations, contact d'exécution)* | **Opérationnelle** |
| **Communication** *(messages, file d'envoi)* | Technique + identifiante |
| **Journal** *(événements, livraisons)* | Technique, **monotone** (**E2**) |
| **Audit** | Technique, **append-only** |
| **Configuration d'agent** | Technique, **immuable** (**IA2**) |

**Un agrégat n'est pas une table — D4** : un agrégat est une **frontière de cohérence métier**. Le nombre d'agrégats ne détermine pas le nombre de tables.

### 14.3 La séparation des cycles — `missions` distincte

*Source : ADR-010 §5 — **décision différée par l'ADR-001, ici tranchée***

**Décision : oui, une relation `missions` distincte de `enquiries`.**

Quatre arguments, **dont le premier suffit à lui seul** :

1. **Confidentialité.** **⛔1** interdit toute donnée commerciale au chauffeur. Si la mission vit dans `enquiries`, l'exécutant lit une ligne portant catégorie, résumé et statut commercial. **RLS filtre des lignes, pas des colonnes** — l'interdiction serait inapplicable.
2. **Cycles de vie distincts.**
3. **Cardinalité** — `status = 'mission'` **[F]** impose une relation de un pour un, alors qu'une demande peut produire plusieurs missions.
4. **Désambiguïsation de `assigned_to`.**

**Correction de l'évaluation de l'ADR-001 §14** : cette séparation était qualifiée de « non réversible, coût élevé ». **C'est inexact.** Aucune mission n'existe, aucune reprise de données n'est nécessaire. **La séparation est purement additive et réversible par simple retrait des nouvelles relations.**

### 14.4 Le contact d'exécution — **D3**

*Source : ADR-010 §6 et §6.1*

**Option retenue** : la mission reçoit une copie **minimale et explicite** des seules informations nécessaires à l'exécution, plutôt qu'un accès à `contacts` via une vue restreinte.

**Quatre conséquences** : un exécutant n'accède **jamais** à `contacts` · l'instantané est daté et purgeable indépendamment · il **survit** à une anonymisation ultérieure de la fiche · **le problème d'autorisation devient un problème de modélisation — et disparaît**.

> **« Le contact d'exécution est figé au moment défini par le processus métier. Il n'est jamais resynchronisé automatiquement avec la fiche CRM. »**

**Tension assumée** : cela duplique de la donnée personnelle. **La contrepartie est une rétention stricte** — purge à la fin du délai de grâce (Q-AE tranchée, ADR-011 §3).

### 14.5 Invariants transversaux — T1 à T6

*Source : ADR-010 §7 — exigence posée par l'ADR-001 §8.4*

| # | Invariant | Garanti où |
|---|---|---|
| **T1** | Aucune mission créée pour une demande annulée ou clôturée | **Base** |
| **T2** | Aucune clôture de demande incompatible avec une mission active | **Base** |
| **T3** | Une annulation produit une conséquence opérationnelle | **Domaine + événement** |
| **T4** | Cohérence des données partagées entre les deux cycles | **Base + copie explicite** |
| **T5** | Une affectation n'existe que pour une mission existante | **Base** |
| **T6** | Une mission a exactement une demande d'origine | **Base** |

> **Règle générale : un invariant qui engage deux agrégats est garanti en base ; un invariant qui déclenche une suite appartient au domaine.** T3 est le seul du second type — et c'est précisément celui qu'une base ne doit pas porter (**E1**).

### 14.6 Inventaire cible des relations

**Existantes, conservées** **[F]** : `contacts` · `organizations` · `enquiries` · `enquiry_details` · `enquiry_notes` · `communications` · `communication_messages` · `audit_logs` · `feature_flags` · `enquiry_reference_counters`.

| Vague | Relations | Nombre |
|---|---|---|
| **CRM** | `actors` · `role_assignments` · `schema_migrations` | **3** |
| Événements | `domain_events` · `event_deliveries` · `idempotency_keys` | 3 |
| Chauffeur | `missions` · `assignments` | 2 |
| Partenaires | `organization_members` | 1 |
| Client | *(lien contact ↔ acteur — attribut nullable)* | 0 ou 1 |
| IA | `agent_configurations` · `agent_prompt_versions` | 2 |

**Contrôle anti-sur-modélisation** : le seuil d'alerte de l'ADR-002 était fixé à plus de six entités d'identité pour le sprint CRM. **Le compte est de deux.**

**`payments` n'est pas modélisé** tant que Q-A n'est pas tranchée. **Aucune donnée bancaire sensible n'est stockée** (Q4).

### 14.7 Identifiants — trois niveaux — **D5**

| Niveau | Nature | Exposition |
|---|---|---|
| **Identifiant interne** | Clé technique | Jamais exposé |
| **Référence métier interne** | Traçabilité pour les équipes | Interne uniquement |
| **Référence publique opaque** | Exposée au client, chauffeur, partenaire | **Non séquentielle, non dérivable** |

> **« Une référence publique ne doit pas permettre d'inférer le volume d'activité, la chronologie, le nombre de missions, l'identité d'un client ou la relation entre plusieurs dossiers. »**

**Révision rétroactive** : le format séquentiel de la référence de demande existante **[F]** est précisément ce qui rend le reçu énumérable. **L'opacité devient un invariant de sécurité, non une préférence de format.**

### 14.8 Fin de vie

**Règle : aucune suppression physique sur un agrégat métier.**

| Objet | Fin de vie |
|---|---|
| Acteur | **Jamais supprimé** — statut désactivé (**I10**) |
| Contact | **Anonymisé** |
| Demande, mission | Statut d'annulation ou de clôture |
| Événement, audit | **Jamais individuellement** (**E2**) |
| Clé d'idempotence | Supprimable à l'échéance |
| Contact d'exécution | **Purgeable** après le délai de grâce |
| Configuration d'agent | Jamais — statut retiré |

**Historisation — décision de sobriété** : le **journal d'événements est l'histoire**. `audit_logs.old_values` **[F]** n'est renseigné que pour les **actions sensibles**. **Tenir deux historiques parallèles produirait surtout deux occasions de divergence.**

### 14.9 Migrations — **D6**

*Source : ADR-010 §15 et §15.1*

**Défaut constaté** **[F]** : aucune table de suivi ; le script réapplique **toutes** les migrations à chaque exécution.

**Six décisions** : table de suivi *(identifiant, empreinte, date)* · **amorçage sans rejeu** des cinq migrations existantes **[F]** · **sens unique**, correction par migration compensatoire · **empreinte divergente = erreur bloquante** · RLS dans la même migration que la création · staging avant production.

> **Sur des données de production, un retour automatique est plus dangereux que le mal qu'il répare.**

**Auditabilité — D6** : chaque application enregistre identifiant · **empreinte** · date · **environnement** · **origine ou auteur** · **résultat** · version de référence. **Production et Staging possèdent leur propre registre.**

### 14.10 Compatibilité ascendante

**Motif étendre-puis-réduire**, en quatre temps : ajouter sans contraindre → écrire dans les deux formes → migrer les données → retirer l'ancienne forme.

**Conventions de nommage** : reprise stricte de l'existant **[F]**, sans renommage. **Pas de préfixe de zone dans les noms** — une zone est une propriété de gouvernance, appliquée par les privilèges.

---

## 15. Sécurité

*Source : ADR-011* — voir également §7 (Autorisation) et §6 (Authentification).

### 15.1 Qualification des actions sensibles — la règle avant la liste

**Une action est sensible si elle satisfait au moins un critère :**

| # | Critère |
|---|---|
| **C1** | Modifie des droits, des rôles ou une identité |
| **C2** | Produit un effet externe irréversible — envoi, paiement, publication |
| **C3** | Expose, exporte ou détruit des données personnelles, notamment en volume |
| **C4** | Modifie une configuration de sécurité ou d'agent |
| **C5** | Déroge à un processus normal |
| **C6** | Engage financièrement |

> **La règle prime sur la liste** : une action nouvelle est qualifiée par les critères, sans attendre une mise à jour d'inventaire.

**Matrice de référence** — extrait des actions exigeant MFA : modification de rôle · création d'acteur · **export** · **purge ou anonymisation** *(+ approbation d'un tiers)* · modification de configuration d'agent · **élévation d'un niveau d'autonomie IA** *(+ approbation)* · **opération financière** · usage d'un compte d'urgence *(+ revue obligatoire)*.

### 15.2 Rétentions tranchées

*Source : ADR-011 §3 — **toutes les durées sont des recommandations par défaut au titre de SEC1***

| Objet | Durée recommandée | Question |
|---|---|---|
| Délai de grâce partenaire | **7 jours**, lecture seule | Q-M |
| Contact d'exécution | **Fin du délai de grâce** — même horloge que l'accès | Q-AE |
| Désignation nominative obligatoire | **Dès le deuxième membre actif** | Q-L |
| Événements métier | **24 mois en ligne, 5 ans au total** | Q-W |
| Livraisons terminées | **90 jours** | Q-W |
| Mémoire de tâche IA | **Fin de tâche, 24 h maximum** | Q-AD |
| Contenu d'une proposition rejetée | **30 jours** *(métadonnées durables)* | Q-AD |
| Approbation IA — effet externe | **1 heure** *(interne : 24 h)* | Q-AB |
| Anonymisation du libellé d'acteur | **12 mois** *(suppression : jamais)* | Q-G |
| Adresse IP d'audit | **12 mois** — **en place** **[F]** | — |

### 15.3 Les durées sont des paramètres — **SEC1**

> **« L'architecture décide des catégories de conservation, des événements déclencheurs et des règles de gouvernance. Les durées numériques constituent des paramètres de politique d'exploitation et peuvent évoluer sans remettre en cause les décisions architecturales. »**

**Ce qui reste architectural** : la rétention du contact d'exécution **est alignée sur le droit d'accès** · le **fait** et son **enregistrement de livraison** ont des durées distinctes · le **contenu** et la **mesure** d'une proposition IA ont des durées distinctes · l'anonymisation précède, la suppression n'arrive jamais · purge et archivage portent sur des **ensembles**.

### 15.4 RGPD — effacement à trois couches

*Source : ADR-002 §8.5, ADR-011 §5*

| Couche | Traitement à l'effacement |
|---|---|
| Compte de connexion | **Supprimé** |
| Contact CRM | **Anonymisé** |
| Acteur + journal d'audit | **Conservés**, identifiant seul |

> **Un identifiant d'acteur, une fois toute donnée identifiante supprimée, est un pseudonyme et non une donnée personnelle.** C'est ce qui permet de concilier effacement et intégrité de l'audit.

### 15.5 Secrets — **SEC4**

**Chaque secret porte** : identifiant · **propriétaire fonctionnel** · runtime utilisateur · environnement · **procédure de rotation** · fréquence de revue · état.

> **« Aucun secret ne reste sans propriétaire clairement identifié. »** Un secret sans propriétaire n'est jamais tourné : personne ne s'estime responsable, et personne ne sait ce qui casserait.

### 15.6 Exports — **SEC3**

**Chaque export est un objet métier temporaire gouverné**, porteur de : identifiant · **propriétaire** · **finalité** · date de création · **date d'expiration** · **journal des téléchargements** · état.

> **Un export sans propriétaire est une copie de la base hors de ses protections, que personne ne surveille et que rien ne fait disparaître.**

### 15.7 Sauvegardes et archives — **SEC2**

| | **Sauvegarde** | **Archive** |
|---|---|---|
| Finalité | **Restauration après incident** | **Conservation documentaire** |
| Consultable comme historique | **Non, jamais** | Oui, selon les droits |
| Accès | **Très restreint** | Selon autorisation |

> **« Une sauvegarde n'est jamais un mécanisme de consultation historique. »**

**Position sur l'effacement** : les sauvegardes ne sont pas retraitées lors d'un effacement, **à trois conditions** — durée **bornée et documentée** · toute restauration **rejoue les effacements intervenus depuis** · accès aussi protégé que la base.

> **Une restauration qui ressusciterait des données effacées serait une violation, non un incident technique.**

### 15.8 Incidents — **SEC5**

**Tout incident est qualifié avant traitement**, selon cinq catégories : **Sécurité** · **Disponibilité** · **Intégrité** · **Confidentialité** · **Conformité**.

**La qualification détermine qui est alerté, dans quel délai, et si une notification externe est requise.**

**Compte d'urgence** : un seul, nominatif, avec MFA, identifiant scellé. Son usage est une **action sensible auditée**, impose une **revue obligatoire** et une **rotation systématique de l'identifiant**. **Un compte d'urgence dont l'usage ne déclenche rien n'est qu'une porte dérobée.**

### 15.9 Risques de production traités

*Source : ADR-011 §7*

| Risque **[F]** | Décision |
|---|---|
| **Staging pouvant envoyer réellement** | Blocage par défaut + liste explicite — **le plus urgent** |
| **Reçu public énumérable** | Jeton opaque (**D5**), suppression de l'oracle 404/200 |
| **Écriture anonyme sur référence arbitraire** | Même preuve de possession |
| **PII et consentements en stockage local** | Durée de vie bornée |
| **IP réelle derrière mandataire** | Liste de mandataires de confiance — **Q1** |
| **Honeypot neutralisé** | **Déclassé** — n'est plus compté comme protection |
| **CMP sur réseau de diffusion, version flottante** | Auto-hébergement · version figée · intégrité · non bloquant |
| `/health` exposant version et environnement | Liveness minimale seule |

**Sur le CMP** : **l'indisponibilité du CMP ne doit jamais ouvrir la mesure d'audience ; elle doit la fermer.**

### 15.10 Risque résiduel structurel

> **`service_role` reste lisible par tout plugin WordPress** **[F]**. **Aucune solution technique n'existe dans WordPress.** Seule la gouvernance des plugins y répond : inventaire · provenance vérifiée · **installation soumise à décision** · mise à jour suivie · **le nombre de plugins est en soi un indicateur de sécurité**.

---

## 16. Observabilité

*Source : ADR-012*

### 16.1 Les quatre journaux

| Journal | Finalité | Transaction | Accès | Données personnelles |
|---|---|---|---|---|
| **Audit métier** | Imputabilité, valeur probante | **Même transaction si l'imputabilité l'exige** | Interne restreint ; **lecture en masse = action sensible** | Minimales |
| **Événement métier** | Fait, histoire | **Atomique** avec l'état | Runtimes de confiance | **Charge minimale** |
| **Journal technique** | Diagnostic, mesure | **Hors transaction — ne bloque jamais** | Exploitation | **Aucune inutile** |
| **Journal de sécurité** | Détection, preuve d'incident | **Mécanisme séparé** | **Très restreint** | Minimales |

**Contenu propre du journal de sécurité** : échecs d'authentification · **refus d'autorisation** · **refus de la liste d'outils IA** · événements de facteur d'authentification · **usage d'un compte d'urgence** · rotation de secret · déploiement ou retrait de policy · export et purge · **envoi bloqué par l'environnement**.

> **Ces événements intéressent la sécurité même quand tout fonctionne normalement.** Un refus d'autorisation isolé est normal ; leur accumulation est un signal.

**Immutabilité — O3** : le journal de sécurité est **append-only**. **Un journal de sécurité modifiable ne prouve rien.** Une entrée erronée se corrige par une **entrée rectificative**.

### 16.2 Corrélation — six identifiants — **O1**

| Identifiant | Portée | Réutilisation |
|---|---|---|
| **`correlation_id`** | **Le flux entier** | **Jamais** |
| `request_id` | Un appel | **Jamais** |
| `causation_id` | Un lien de cause | **Oui** — une cause a plusieurs effets |
| `actor_id` | Permanente | **Oui, par nature** |
| `event_id` | Un fait | **Jamais** (**E2**) |
| `idempotency_key` | (principal, type) | **Oui, délibérément, sur rejeu de la même intention** |

> **« Un identifiant conserve la même signification pendant tout son cycle de vie. »**

**Un webhook produit une commande, qui produit un événement, qui produit une livraison, qui appelle un fournisseur : quatre requêtes, une seule corrélation.**

**Les parcours anonymes sont corrélés comme les autres** : `actor_id` nul, corrélation présente. **C'est ce qui distingue « acteur inconnu » de « opération introuvable ».**

### 16.3 Confidentialité des journaux

> **Un journal est un stockage de données comme un autre.**

L'audit métier est une **duplication inter-zone** au sens de **D2**. **Aucun journal n'est accessible à un consommateur externe, jamais.**

**Recommandation fondée sur un fait vérifié** : le masquage actuel repose sur une **liste de refus par nom de clé**, et le code documente lui-même sa limite **[F]**. **Une liste de refus finit toujours par laisser passer quelque chose.** Cible : **liste d'autorisation**.

### 16.4 Métriques — **O2**, **O4**

**Cinq familles, organisées par la question posée** : le système fonctionne-t-il ? · le travail s'écoule-t-il ? · quelque chose échoue-t-il en silence ? · sommes-nous attaqués ? · la qualité se dégrade-t-elle ?

> **« Les tableaux de bord, métriques et indicateurs servent exclusivement à l'observation. Ils ne constituent jamais une source de vérité métier. »** (**O2**)

> **« Les métriques sont agrégées et ne portent jamais de données personnelles directement identifiantes. »** (**O4**)

**Tous les indicateurs sont interrogeables depuis les tables existantes ou décidées. Aucune infrastructure nouvelle.**

### 16.5 Alertes — deux règles et un cycle de vie

| Niveau | Délai | Exemples |
|---|---|---|
| **Critique** | Immédiat | Battement absent · **envoi réel hors Production** · pic de refus d'autorisation · compte d'urgence |
| **Anormal** | Dans la journée | File anormalement longue · taux d'échec fournisseur · écart de réconciliation |
| **Information** | Tableau de bord | Volumes, tendances |

**R1 — L'absence de signal doit alerter.** *(ADR-012 §7)* **Surveiller les erreurs ne suffit pas ; il faut surveiller les silences.**

**R2 — Une alerte sur laquelle personne n'agit doit être supprimée.** L'accoutumance aux alertes est un risque de sécurité.

**Audit des alertes — O5** : toute alerte critique ou anormale conserve identifiant · règle déclenchante · date · niveau · état · **date et auteur de reconnaissance** · date de clôture · **motif**. **Sans ce cycle de vie, R2 reste un vœu.**

### 16.6 Réconciliation — Q-X tranchée

| Fréquence | Contrôles |
|---|---|
| **Quotidienne** | Files, livraisons bloquées, événements sans livraison, échecs définitifs, envois bloqués par l'environnement |
| **Hebdomadaire** | Cohérence état/événement, purges dues, qualité des données, secrets à revoir |

**Lecture seule, rapport, jamais de correction automatique** (**E4**). **Un écart persistant sur deux exécutions consécutives devient une alerte « anormal ».**

### 16.7 Seuils tranchés

| Question | Décision |
|---|---|
| **Q-Z** — âge du plus ancien élément en attente | **15 minutes** anormal · **60 minutes** critique — **déduits de la promesse commerciale**, non d'un chiffre arbitraire |
| **Q-R** — `/health` | **Liveness minimale publique** ; readiness et diagnostic **réservés** |

### 16.8 Les cinq natures de trace

| Nature | Valeur probante |
|---|---|
| **Événement métier** | **Oui** — c'est l'histoire |
| **Audit métier** | **Oui** |
| **Journal de sécurité** | **Oui**, sous condition d'immutabilité |
| **Journal technique** | **Non** |
| **Métrique** | **Non, jamais** |

**Ce que le modèle interdit explicitement** : traiter un journal technique comme un audit probant · fonder une décision métier sur une métrique (**O2**) · porter une donnée identifiante dans une métrique (**O4**) · retoucher une entrée du journal de sécurité (**O3**) · corriger un écart depuis un contrôle de qualité (**E4**).

---

## 17. Continuité de service

*Source : ADR-013*

### 17.1 L'inversion de criticité

| Service | Criticité **aujourd'hui** | Criticité **après l'Application Chauffeur** |
|---|---|---|
| Formulaire public | **Élevée** — c'est l'acquisition | Élevée |
| Back-office | Moyenne | Moyenne |
| **Application Chauffeur** | *(n'existe pas)* | **Critique — une mission en cours n'attend pas** |
| Espace client, portail | *(n'existent pas)* | Faible |
| Agents IA | *(n'existent pas)* | **Nulle — assistifs par construction** |

**Contrainte de conception imposée** :

> **Le chemin du chauffeur ne doit jamais dépendre de WordPress.** Ce n'est pas un effet secondaire à préserver par vigilance : **c'est une propriété à ne jamais compromettre par commodité.**

### 17.2 Objectifs de continuité — **CT1**

**L'architecture ne fixe pas de valeurs numériques** (**SEC1**). Elle impose que **chaque service critique possède des objectifs RTO et RPO documentés**.

**L'IA est le seul composant dont l'indisponibilité prolongée est sans conséquence** — objectifs illimités. C'est une propriété voulue.

### 17.3 Point de défaillance unique

> **PostgreSQL / Supabase est le point de défaillance unique de l'ensemble.**

Aucune décision du corpus ne l'a créé — c'était déjà vrai au Sprint 1 **[F]** — et aucune ne l'a supprimé. **L'architecture hybride en augmente la portée.**

**Il est préférable de l'énoncer que de le laisser implicite.** **Une redondance applicative serait un surdimensionnement à l'échelle de Q6** ; la réponse proportionnée est de **savoir restaurer vite et sûrement**.

### 17.4 Sauvegardes — point d'exploitation majeur

| Domaine | Contient | Ne contient pas |
|---|---|---|
| **Supabase** | **Toutes les données métier** | Aucun élément WordPress |
| **WordPress** | Fichiers, thème, extensions, comptes internes, options **[F]** | **Aucune donnée métier** **[F]** |

> **Une sauvegarde du système de fichiers WordPress contient `wp-config.php`, donc la clé de service** **[F]**. **Elle doit être protégée au même niveau que la base elle-même.**

### 17.5 Restauration

> **Une sauvegarde jamais restaurée est une hypothèse, pas une garantie.**

**Exigences** : test périodique réel **sur staging** · **effacements rejoués** · réconciliation après restauration · vérification du registre de migrations (**D6**) · **ne jamais restaurer d'anciens secrets — reprendre ceux en vigueur**.

### 17.6 Exercices de continuité — **CT4**

| Exercice | Vérifie |
|---|---|
| **Restauration** | Que la sauvegarde est exploitable et que les effacements sont rejoués |
| **Rotation de secret** | Que les étapes 4 et 5 fonctionnent réellement *(Q1)* |
| **Perte de WordPress** | Que le chemin chauffeur survit |
| **Perte de Supabase** | Le seul scénario réellement critique |
| **Indisponibilité d'un fournisseur** | Que la stratégie de dégradation **CT2** se comporte comme prévu |

### 17.7 Dépendances externes — **CT2**

> **Toute dépendance externe est supposée pouvoir devenir indisponible.** Pour chacune, une stratégie documentée : **attente · reprise · mode dégradé · intervention humaine**.

> **Une dégradation doit fermer, jamais ouvrir.**

### 17.8 Procédures opératoires — **CT3**, **CT5**

**État actuel** **[F]** : une checklist de déploiement existe ; le document d'exploitation **précise lui-même ne couvrir que staging**. **Aucune procédure de production n'est documentée.**

**Huit procédures à écrire** : déploiement en production · **rotation d'un secret** · **restauration** · traitement d'incident par catégorie · application d'une migration · purge et export · usage d'un compte d'urgence · bascule d'un handler.

**Versionnement — CT3** : chaque procédure porte identifiant · version · auteur · date de validation · dernière révision · **dernière exécution réussie**.

> **Une procédure dont la dernière exécution réussie est ancienne — ou inexistante — est une hypothèse documentée, pas une capacité.**

**Responsabilités — CT5** : toute procédure précise **qui détecte · qui décide · qui exécute · qui valide le retour à la normale**. Les quatre rôles peuvent être tenus par une même personne ; **ce qui est interdit, c'est l'implicite**. Le rôle de **validation du retour à la normale** est le plus souvent omis.

### 17.9 Supervision quotidienne — six contrôles

1. **Le battement de l'ordonnanceur est-il présent ?**
2. Quel est **l'âge du plus ancien élément en attente** ?
3. Y a-t-il de **nouveaux échecs définitifs** ?
4. Le **rapport de réconciliation** est-il propre ?
5. Le **journal de sécurité** présente-t-il une anomalie ?
6. Y a-t-il eu des **envois bloqués par l'environnement** ? *(normal en staging — **anormal en production**)*

### 17.10 Statut de Q1

**Q1 est passée de blocage d'architecture à blocage d'exploitation.** Elle bloque désormais quatre points : étapes 4 et 5 de la rotation · limitation de débit fondée sur l'IP réelle · choix de la voie d'ordonnancement · fiabilité des transients. **Elle reste nécessaire avant la mise en service de l'Application Chauffeur.**

---

## 18. Déploiement

*Source : ADR-014*

### 18.1 Ordre de déploiement — cinq étapes

```
1. Migration d'EXTENSION        ← compatible avec le code encore en place
2. Déploiement du CODE          ← rollback toujours possible
3. ACTIVATION par drapeau       ← découple « déployé » de « actif »
4. OBSERVATION                  ← supervision quotidienne
5. Migration de RÉDUCTION       ← livraison ultérieure, jamais la même
```

**L'étape 3 est la clé** : elle sépare le moment où le code arrive de celui où la fonctionnalité agit. **Sans elle, tout déploiement est une mise en service.**

**L'étape 5 dans une livraison ultérieure** garantit qu'à tout instant, la version précédente du code reste compatible avec le schéma en place.

**Ni bleu-vert, ni canari** — surdimensionnement à l'échelle de Q6.

### 18.2 Retour arrière — quatre objets, quatre stratégies

| Objet | Retour arrière | Délai |
|---|---|---|
| **Fonctionnalité** | **Extinction du drapeau** | **Immédiat, sans redéploiement** |
| Code WordPress | Redéploiement de la version précédente | Minutes |
| Edge Function | Redéploiement autonome, par fonction | Minutes |
| **Schéma** | **Aucun** — migration compensatoire | Heures, réfléchi |

> **Le retour arrière le plus rapide n'est pas un redéploiement : c'est l'extinction d'un drapeau.**

> **Règle absolue : le code doit toujours pouvoir revenir en arrière.** Toute livraison qui exigerait un schéma que la version précédente ne tolère pas est **refusée** — non parce qu'elle échouerait, mais parce qu'elle supprimerait l'issue de secours au moment où elle serait nécessaire.

### 18.3 Drapeaux — **DPL2**

**Défaut sûr** : drapeau absent, illisible ou indéterminé ⇒ **désactivé**.

| | **Drapeau de déploiement** | **Option produit** |
|---|---|---|
| Finalité | Contrôler une mise en service | Configurer une offre |
| Durée de vie | **Temporaire — il doit mourir** | Permanente |

**Chaque drapeau porte** : **propriétaire fonctionnel** · objectif · date de création · **type** · **date prévue de retrait** · **justification documentée** s'il est conservé au-delà.

> **Un drapeau temporaire dépassant sa date n'est pas supprimé d'office — il doit être justifié**, ce qui oblige à choisir entre le retirer et le reclasser en option produit.

### 18.4 Activation progressive — quatre axes

| Axe | Mécanisme |
|---|---|
| **Par environnement** | Staging puis production |
| **Par population** | Interne d'abord, externes ensuite |
| **Par handler** | Bascule un par un, workers coexistants **[F]** |
| **Par table** | Policies déployées une par une |

**Aucun ne demande d'infrastructure de répartition de trafic.**

### 18.5 Critères de passage en production — dix critères vérifiables

1. **Les tests ont réellement été exécutés** **[F]** · 2. **Tests d'autorisation passés sur base réelle** · 3. **Garde-fou d'environnement vérifié** (**E9**) · 4. Migrations appliquées en staging, registre cohérent (**D6**) · 5. Recette staging conforme · 6. **Procédure de retour arrière identifiée** · 7. Drapeaux dans l'état attendu · 8. Aucun secret introduit dans le dépôt · 9. Si l'autorisation est touchée : **revue de policies** · 10. **Accord explicite du responsable de validation** **[F]**.

### 18.6 Manifeste de livraison — **DPL1**

> **« Toute livraison est accompagnée d'un manifeste de livraison versionné. »**

**Huit éléments** : identifiant · **périmètre annoncé** · versions concernées · migrations incluses · **drapeaux concernés** · critères de validation · **procédure de retour arrière** · décision finale.

**Le lot v1.0.1 en a produit un de fait** **[F]** — **la procédure existe déjà en pratique ; il reste à l'écrire.**

### 18.7 Identifiant de déploiement — **DPL3**

Réutilisé dans l'observabilité, les audits, les incidents et les rapports. Il suit **O1** — il ne change jamais de signification.

**Son intérêt est de répondre à la question la plus fréquente en incident : « qu'est-ce qui a changé juste avant ? »**

### 18.8 Validation post-déploiement — **DPL5**

> **Le déploiement n'est pas terminé à la mise en ligne : il l'est à la validation.**

Tant que la validation n'est pas achevée : **la procédure de retour arrière reste immédiatement applicable** · la livraison est **en observation**.

### 18.9 Environnements et parité — **DPL4**

**Parité exigée** sur : version de PHP · extensions installées · forme de la configuration · capacités d'ordonnancement · présence d'un cache objet.

> **La parité ne signifie pas identité absolue, mais cohérence documentée.** Un écart connu et justifié est acceptable ; **un écart ignoré ne l'est pas.**

**Écart actuel** **[F]** : la version de PHP réellement exécutée en production est **inconnue** (Q-AK).

**Rien n'est jamais partagé entre environnements** — ni secret, ni ordonnanceur, ni worker, ni registre. **Un partage, même temporaire « pour dépanner », est un incident de configuration.**

### 18.10 Cas particulier de la PWA

> Un client installé peut exécuter une version ancienne pendant des jours, et soumettre des commandes forgées par cette version. **Le serveur doit donc tolérer les versions récentes précédentes** (Q-AJ).

---

## 19. Qualité

*Source : ADR-015*

### 19.1 Le défaut fondateur

> **Une intégration continue verte ne prouve pas aujourd'hui que les tests ont été exécutés.** **[F]**

L'installation des dépendances tolère son propre échec, et l'exécution des tests est conditionnée à la présence du binaire **[F]**. Un échec d'installation produit une **construction verte avec zéro test exécuté**.

**Principe fondateur** :

> **Aucune étape ne peut absorber son propre échec. L'absence d'exécution d'un test est un échec, jamais une omission.**

> **Une chaîne qui ment est pire qu'une chaîne absente : elle produit une confiance imméritée.**

### 19.2 Les six niveaux de test

| Niveau | Environnement | État |
|---|---|---|
| **Domaine pur** | PHP seul | **Existe** **[F]** |
| **Contrats** | Doubles | Partiel **[F]** |
| **Intégration applicative** | Doubles en mémoire **[F]** | **Existe** |
| **Autorisation** | **PostgreSQL réel** | **N'existe pas** |
| **API** | WordPress réel ou harnais | **N'existe pas** **[F]** |
| **Exploitation** | Staging | **N'existe pas** |

> **Les trois niveaux manquants sont exactement ceux qui portent le risque introduit par ce programme.**

### 19.3 Couverture

> **La couverture est un indicateur, jamais un objectif.** Environ 70 % sur la logique métier critique, **pas uniformément**.

| Périmètre | Métrique pertinente |
|---|---|
| Domaine | Couverture de lignes |
| **Autorisation** | **Complétude des cas négatifs, pas un pourcentage** |
| API | Chaque route × chaque classe d'erreur |
| Outils IA | Chaque outil × validation, autorisation, effet, audit |

> **Un jeu de policies couvert à 100 % sans test négatif ne prouve rien** — il prouve que le code s'exécute, pas qu'il refuse.

### 19.4 Tests d'autorisation

**Exigence structurante : une base PostgreSQL réelle dans la chaîne.** Les doubles en mémoire **[F]** n'exécutent pas RLS.

**Deux règles bloquantes** :
- **Une table avec RLS activée et sans test de policy fait échouer la construction.**
- **Un test de refus qui cesse d'échouer est une alerte de sécurité**, traitée comme une régression bloquante.

### 19.5 Gouvernance CI/CD — neuf étapes, toutes bloquantes

1. **Reproductibilité** — verrou versionné, installation qui doit réussir
2. **Analyse statique**
3. Unitaires et intégration sur doubles
4. **Autorisation sur PostgreSQL réel** — *le plus structurant*
5. **Contrats d'API**
6. **Migrations** — exécution, idempotence, RLS, compatibilité arrière
7. **Sécurité** — secrets, dépendances, **zones interdites aux hooks**
8. **Cohérence** — en-tête de plugin contre constante, versions déclarées
9. **Couverture mesurée et publiée**

> **Règle transversale : aucune étape n'est conditionnée au succès d'une étape optionnelle antérieure.** C'est la mécanique exacte du défaut actuel **[F]**.

### 19.6 Douze obligations avant mise en production

1. **La chaîne est verte ET prouve que les tests ont été exécutés** · 2. Tests d'autorisation pour toute table touchée · 3. **Garde-fou d'environnement vérifié** (**E9**) · 4. Migrations en staging, registre cohérent (**D6**) · 5. Recette staging conforme · 6. **Manifeste complet** (**DPL1**) · 7. **Identifiant de déploiement attribué** (**DPL3**) · 8. **Procédure de retour arrière applicable** (**DPL5**) · 9. Drapeaux dans l'état attendu (**DPL2**) · 10. **Exercice de restauration récent** (**CT3**, **CT4**) · 11. **Procédure de rotation exécutée au moins une fois** · 12. **Accord explicite du responsable de validation** **[F]**.

> **Les obligations 10 et 11 sont aujourd'hui impossibles à satisfaire** : ni restauration ni rotation n'ont jamais été exécutées **[F]**. **C'est la raison pour laquelle elles figurent ici.**

### 19.7 Qualité documentaire

> **La dérive documentaire est un mode de défaillance observé, pas théorique** **[F]** : un fichier de documentation annonce des classes qui n'existent pas, un autre référence des composants supprimés.

**Contrôles automatisables** : référence d'ADR inexistante · lien interne rompu · **contrat déclaré Stable sans suite de tests** (**G2**) · **hook de niveau 3 sans documentation** (**G4**) · **procédure sans date de dernière exécution réussie** (**CT3**) · **drapeau temporaire au-delà de sa date sans justification** (**DPL2**).

### 19.8 Tests continus et exercices périodiques

| | **Test d'intégration continue** | **Exercice périodique** |
|---|---|---|
| Fréquence | Chaque livraison | Calendrier (**CT4**) |
| Exemples | Domaine, autorisation, API, migrations | **Restauration · rotation · perte de runtime · indisponibilité fournisseur** |
| Effet | Bloque la livraison | **La fraîcheur du dernier exercice réussi est un critère de livraison** |

---

## 20. Relations entre ADR

### 20.1 Graphe de dépendance

```
ADR-001 (Runtimes)
   │
   ├──► ADR-002 (Identité) ──┬──► ADR-003 (Authentification)
   │                          │        │
   │                          └────────┴──► ADR-004 (Autorisation) ◄── Cartographie
   │                                              │
   │                                              ├──► ADR-005 (API)
   │                                              │       │
   │                                              │       └──► ADR-006 (Extensibilité)
   │                                              │              │
   │                                              │              └──► ADR-007 (Événements)
   │                                              │                     │
   │                                              │                     └──► ADR-008 (Exécution)
   │                                              │                            │
   │                                              └────────────────────────────┴──► ADR-009 (IA)
   │                                                                                  │
   └──────────────────────────────────────────────────────────────────────────────────┴──► ADR-010 (Données)
                                                                                              │
                                                                                              └──► ADR-011 (Sécurité)
                                                                                                     │
                                                                                                     └──► ADR-012 (Observabilité)
                                                                                                            │
                                                                                                            └──► ADR-013 (Continuité)
                                                                                                                   │
                                                                                                                   └──► ADR-014 (Déploiement)
                                                                                                                          │
                                                                                                                          └──► ADR-015 (Qualité) ── CLÔT LE CORPUS
```

**Chaque ADR dépend de toutes celles qui la précèdent.** L'ADR-015 est **la seule applicable immédiatement, sans attendre aucune autre — et la seule dont l'absence rend toutes les autres invérifiables.**

### 20.2 Matrice « bloque / est bloquée par »

| ADR | Dépend de | Bloque explicitement |
|---|---|---|
| **001** | — | Toutes |
| **002** | 001 | 003, 004, 009, 010 |
| **003** | 001, 002, Cartographie identité | 004, 009, 011 |
| **004** | 001–003, Cartographie identité | 009, 010, 011, 015 |
| **005** | 001–004, deux cartographies | 007, 009, 010, 015 |
| **006** | 001–005 | 007 + organisation des futurs modules |
| **007** | 001–006 | 008, 009, 010, 012 |
| **008** | 001–007 | Application Chauffeur — tranche **Q-V** |
| **009** | 001–008 | 010 *(configuration d'agent)*, 011 *(matrice des actions sensibles)* |
| **010** | 001–009 | — *(huit décisions y ont été renvoyées)* |
| **011** | 001–010 | — *(tranche dix questions)* |
| **012** | 001–011 | Alimente 015 — tranche **Q-X**, **Q-Z**, **Q-R** |
| **013** | 001–012 | — |
| **014** | 001–013 | Alimente 015 |
| **015** | 001–014 | **Clôt le corpus** |

### 20.3 Décisions déléguées d'une ADR à une autre

| Origine | Décision déléguée | Destination | État |
|---|---|---|---|
| ADR-001 §8.4 | Traduction physique de la séparation des cycles | ADR-010 §5 | **Tranchée** — `missions` distincte, additive |
| ADR-001 §8.4 | Recensement des invariants transversaux | ADR-010 §7 | **Tranchée** — T1 à T6 |
| ADR-001 §14 | Coût et réversibilité de la séparation | ADR-010 §5 | **Tranchée** — évaluation initiale **corrigée** |
| ADR-001 §17 | Modèle d'identité (Q-C) | ADR-002 §6 | **Tranchée** — `actors.id` |
| ADR-002 §7 bis | Forme de l'identifiant de corrélation | ADR-010, ADR-012 §3 | **Tranchée** — six identifiants |
| ADR-002 §13 | Q-G, Q-H | ADR-011, ADR-004 | **Tranchées** |
| ADR-003 §6.5 | Application du MFA | ADR-011 §3 | **Tranchée** |
| ADR-004 §3.1 | Q-L — seuil de désignation | ADR-011 §3 | **Tranchée** |
| ADR-004 §3.3 | Q-M — durées d'accès partenaire | ADR-011 §3 | **Tranchée** |
| ADR-004 §14 | Base PostgreSQL réelle en CI | ADR-015 §5 | **Tranchée** |
| ADR-005 §15 | Q-P — durée des clés d'idempotence | ADR-010 §12 | **Ouverte** |
| ADR-005 §7.1 | Q-R — exposition de `/health` | ADR-012 §13 | **Tranchée** |
| ADR-007 §22 | Q-V — où vit le worker | ADR-008 §4 | **Tranchée** |
| ADR-007 §22 | Q-W — rétention du journal | ADR-011 §3 | **Tranchée** |
| ADR-007 §22 | Q-X — réconciliation | ADR-012 §12 | **Tranchée** |
| ADR-008 §18 | Q-Y — garde-fou d'environnement | ADR-011 §3 | **Tranchée** |
| ADR-008 §18 | Q-Z — seuil d'âge de file | ADR-012 §13 | **Tranchée** |
| ADR-009 §25 | Q-AB, Q-AD | ADR-011 §3 | **Tranchées** |
| ADR-010 §25 | Q-AE — rétention du contact d'exécution | ADR-011 §3 | **Tranchée** |
| ADR-014 §20 | Q-AK — matrice de versions PHP | ADR-015 §13 | **Ouverte** — mesure intermédiaire posée |

### 20.4 Corrections d'une ADR par une autre

*Le corpus se corrige lui-même en trois endroits. Ces corrections sont acquises.*

| ADR corrigée | Correction | ADR correctrice |
|---|---|---|
| **ADR-001 §14** | « Séparation des cycles non réversible, coût élevé » → **purement additive, sans reprise de données, réversible** | ADR-010 §5 |
| **ADR-001 §17 / ADR-003** | « Q1 bloque l'Application Chauffeur » → **Q1 ne bloque plus l'architecture** ; un repli par planification Supabase existe | ADR-008 §6 |
| **ADR-008 / ADR-001** | Q1 requalifiée de blocage d'exploitation, portant sur quatre points précis | ADR-013 §13 |

### 20.5 Apports propres de la Cartographie des flux d'autorisation

**A14 et A15 émergent de la mise en regard des surfaces**, et non d'une ADR prise isolément. **C'est l'apport propre de ce document transverse.**

---

## 21. Cartographie générale

### 21.1 Cartographie des responsabilités

| Composant | Responsable de | Jamais responsable de | Source |
|---|---|---|---|
| **PostgreSQL** | Intégrité · unicité · domaines de valeurs · transitions interdites · **T1, T2, T4, T5, T6** · RLS · émission transactionnelle · maintenance base | **Orchestration · décision métier · appel externe** | ADR-001 §8, ADR-008 §3, ADR-010 §19 |
| **Domaine PHP** | Orchestration des cas d'usage · règles composites · **T3** · composition des messages | Frontière de sécurité des consommateurs externes | ADR-001 §8 |
| **RLS** | Visibilité et droits élémentaires **par ligne** | Règle métier composite · protection contre `service_role` | ADR-001 §8, ADR-004 §10 |
| **Edge Functions** | Commandes externes · webhooks signés · passerelle IA · planification indépendante | Second exemplaire du domaine commercial | ADR-001 §8.5 |
| **Workers** | Livraison des événements · traitement de file | Toute décision métier · tout état durable (**E7**) | ADR-008 §3 |
| **Passerelle IA** | Les onze contrôles · **seul lieu d'application des capacités** | Décider à la place du domaine · détenir `service_role` | ADR-009 §19 |
| **Runtime IA** | Quota technique · coût fournisseur · non-déterminisme | Quota métier · autorisation | ADR-009 §19 |
| **Agent (acteur IA)** | Proposer, rédiger, analyser, résumer | **Décider** · accéder à sa configuration (**A14**) · lire le journal d'événements | ADR-009 §19 |
| **Approbateur humain** | Approuver **dans la limite de ses propres droits** (**R1**) | Approuver ce qu'il ne pourrait pas faire lui-même | ADR-009 §19 |
| **Journal d'événements** | Porter l'histoire des transitions | Être doublé par un second historique | ADR-010 §19 |
| **Audit métier** | Valeur probante · imputabilité · `old_values` pour les actions sensibles | Être échantillonné · rejouer le rôle du journal | ADR-010 §19, ADR-012 §17 |
| **Journal de sécurité** | Détection et preuve d'incident | Être modifié ou supprimé ligne à ligne (**O3**) | ADR-012 §17 |
| **Journal technique** | Diagnostic, hors transaction | Bloquer une opération métier (**A16**) | ADR-012 §17 |
| **Métriques / tableaux de bord** | Observer | Fonder une décision métier (**O2**) · porter une donnée identifiante (**O4**) | ADR-012 §17 |
| **Réconciliation** | Mesurer, signaler, produire un rapport | **Corriger** (**E4**) · déclencher une purge | ADR-011 §12, ADR-012 §17 |
| **Registre de migrations** | Reconstituer l'historique exact **de chaque environnement** (**D6**) | Fournir un retour arrière automatique | ADR-010 §19 |
| **Drapeau de fonctionnalité** | Être le retour arrière le plus rapide | Servir de mécanisme d'autorisation (**E8**) | ADR-014 §14 |
| **Chaîne d'intégration continue** | Prouver que les tests ont été exécutés · les neuf étapes bloquantes | **Absorber son propre échec** | ADR-015 §16 |
| **Propriétaire d'un secret** | Procédure de rotation · fréquence de revue · état (**SEC4**) | Rester implicite | ADR-011 §12 |
| **Propriétaire d'un export** | Finalité · expiration · suivi · révocation (**SEC3**) | Produire une copie sans propriétaire | ADR-011 §12 |
| **Propriétaire d'un environnement** | Objectif · stabilité attendue · politique de mise à jour (**DPL4**) | Laisser subsister une divergence **non documentée** | ADR-014 §14 |
| **Responsable de validation** | L'accord explicite · la décision au manifeste (**DPL1**) · la clôture de l'observation (**DPL5**) | Valider quand une obligation vérifiable n'est pas satisfaite | ADR-014 §14, ADR-015 §16 |
| **Quatre rôles d'incident** (**CT5**) | Détecter · décider · exécuter · **valider le retour à la normale** | Rester implicites | ADR-013 §15 |

### 21.2 Cartographie population × chemin d'accès

*Source : Cartographie §3a et §3b*

| Population | Authentification | Accès base | `service_role` | RLS | Écritures | Si acteur désactivé |
|---|---|---|---|---|---|---|
| **Back-office WP** | Session WP + MFA | Via PHP | oui | contournée | Orchestration PHP | Refus applicatif |
| **Formulaire public** | Aucune | Via PHP | **oui → cause du contournement** | contournée | Création de demande | *(sans objet)* |
| **Espace client** | JWT lien magique | PostgREST | non | **oui** | Lecture ; écritures simples | **Refus par RLS** |
| **PWA chauffeur** | JWT mot de passe | PostgREST + commandes | non | **oui** | **Commandes** | **Refus par RLS** |
| **Portail partenaire** | JWT lien magique | PostgREST + commandes | non | **oui** | **Commandes** | **Refus par RLS** |
| **Edge Function cat. A** | JWT revalidé | Sous JWT utilisateur | **non** | **oui** | Commandes dans les droits de l'utilisateur | **Refus par RLS** |
| **Edge Function cat. B** | JWT ou signature | Privilégiée | **oui, sous conditions** | contournée | Commandes multi-table | **Refus avant commande** |
| **Passerelle IA** | Secret d'agent | **Rôle restreint** | **jamais** | **oui** | **Propositions** | Refus |
| **Webhooks** | **Signature** | Via fonction privilégiée | oui | contournée | Commande / événement | *(sans objet)* |
| **Workers** | Secret machine | Direct | oui | contournée | Traitement de file | *(sans objet)* |
| **Admin privées** | Session WP + MFA | Via PHP | oui | contournée | Sensibles | **Refus fermé** |

### 21.3 Les neuf flux de référence

*Source : Cartographie §5*

| Flux | Description | Point de rupture principal |
|---|---|---|
| **A** | Action interne non sensible | Liaison absente → repli **mesuré et visible** |
| **B** | Action interne **sensible** sans liaison d'acteur | **REFUS FERMÉ** — aucun repli silencieux |
| **C** | Lecture client | Contexte absent · acteur désactivé · liaison inexistante |
| **D** | Commande chauffeur | Affectation absente ou close · commande sans clé · invariant violé |
| **E** | Accès partenaire | Organisation inactive · désignation non correspondante · état clos |
| **F** | Action IA non sensible | Agent suspendu · outil hors liste |
| **G** | Action IA **sensible** | Agent suspendu entre approbation et exécution · approbateur sans liaison |
| **H** | Webhook | Non signé · hors fenêtre · déjà traité *(→ réponse idempotente)* |
| **I** | Tâche planifiée | **Jamais** d'acteur de service générique partagé |

**Règle d'enchaînement** *(ADR-007 §3.2)* : **un webhook ne produit jamais directement un effet — il se traduit en commande**, qui suit le chemin normal. C'est ce qui garantit que l'autorisation, l'idempotence et l'audit s'appliquent aussi aux appels entrants.

### 21.4 Les seize cas de refus par défaut

*Source : Cartographie §6*

| # | Cas | Détecté à |
|---|---|---|
| 1 | Acteur absent sur une surface authentifiée | ③ |
| 2 | **Acteur désactivé** | ④ |
| 3 | Contexte absent sur une surface qui l'exige | ⑤ |
| 4 | Contexte incompatible | ⑤ |
| 5 | Rôle non admis dans le contexte | ⑥ |
| 6 | Organisation inactive | ⑦ |
| 7 | Affectation absente, terminée ou hors délai | ⑧ |
| 8 | Désignation nominative ne correspondant pas | ⑧ |
| 9 | **Accès commercial depuis un contexte chauffeur** | ⑤ ∩ ⑥ |
| 10 | **Action sensible sans acteur humain résolu** | ⑩ |
| 11 | Commande répétée sans clé d'idempotence | ⑩ |
| 12 | Agent IA suspendu | ③ ④ — **proposition ET exécution** |
| 13 | Outil IA non autorisé | passerelle |
| 14 | Webhook non signé ou rejoué | ② |
| 15 | **Relation dédiée non vérifiée vis-à-vis de RLS** | **refus de mise en service** |
| 16 | Action WordPress sensible sans liaison d'acteur | ⑩ — Flux B |

**Le cas 15 est de nature différente : c'est un refus de déploiement.**

### 21.5 Impacts par module cible

| Module | Ce que le corpus lui apporte | Ce qu'il attend encore |
|---|---|---|
| **CRM / Back-office** | Reste en PHP · acteur d'audit · événements · trois relations nouvelles | MFA (Q-K appliquée) |
| **Application Chauffeur** | Lectures par RLS · commandes par Edge Function · `missions` distincte · contact d'exécution | **Q1** *(exploitation)* · Q-J · Q-AF · Q-AJ |
| **Application Client** | Lectures par RLS · identité optionnelle et tardive · parcours anonyme préservé | Q-Q *(jeton de reçu)* |
| **Portail partenaires** | Autorisation **par affectation** · séparation des zones · états actif/grâce/clos | **Q-N** |
| **Conciergerie** | Registre de fournisseurs · paramétrage de verticale | **Q-S** |
| **Agents IA** | Passerelle obligatoire · rôle restreint · onze contrôles · N0–N3 | **Q-AA** *(contractuel)* · Q-AC |
| **Paiements** | Frontière B6 · webhook signé · aucun stockage bancaire | **Q-A** — `payments` **non modélisé** |

---

## 22. Questions ouvertes

*Source : liste consolidée de l'ADR-015 §22, qui clôt le corpus*

### 22.1 Questions ouvertes à la clôture du corpus — dix-neuf

| Réf | Question | Nature | Renvoi |
|---|---|---|---|
| **Q1** | Hébergement, ordonnancement, proxy, IP réelle, cache objet | **Vérification** | **Bloque quatre points d'exploitation** — avant l'Application Chauffeur |
| **Q-N** | Une relation ou vue dédiée respecte-t-elle les policies de la table sous-jacente ? | **Vérification technique** | **Bloquante** — enjeu réduit par ADR-010 §6 |
| **Q-O** | Le fournisseur peut-il émettre un contexte fiable par application, et comment le valider ? | **Vérification technique** | Avant l'ouverture des surfaces externes |
| **Q-A** | Prestataire de paiement | **Décision préalable** | `payments` **non modélisé** tant qu'elle n'est pas tranchée |
| **Q-J** | Stockage de session en PWA : SDK ou mandataire de même origine ? | **Prototype comparatif** | Application Chauffeur |
| **Q-P** | Durée de validité d'une clé d'idempotence | Politique | Implémentation |
| **Q-Q** | Forme du jeton de possession du reçu — **doit satisfaire D5** | Implémentation | Refonte du reçu |
| **Q-S** | Paramétrage multi-verticale : attribut de shortcode, configuration, ou point d'extension ? | Conception | Sprint Conciergerie |
| **Q-T** | Durée de support d'un contrat déprécié | Politique | — |
| **Q-U** | Gouvernance des plugins WordPress tiers : inventaire, provenance, mises à jour | Gouvernance | Cadre posé en ADR-011 §8 |
| **Q-AA** | Fournisseur de modèle IA — localisation, contrat, sous-traitance | **Contractuelle et réglementaire** — **plus architecturale** (**IA1**) | Préalable à tout traitement de données clientes |
| **Q-AC** | Ouverture d'une conversation directe client | **Décision produit** | Arbitrage séparé |
| **Q-AF** | Une demande peut-elle produire plusieurs missions dès la première livraison ? | Conception | Sprint Chauffeur |
| **Q-AG** | Support du journal de sécurité : table dédiée ou dispositif externe ? | Arbitrage | Dépend du niveau de menace retenu |
| **Q-AH** | Garanties réelles du plan Supabase souscrit | **Vérification, prioritaire** | — |
| **Q-AI** | Périmètre et destination des sauvegardes WordPress *(elles contiennent la clé de service)* | **Vérification, prioritaire** | — |
| **Q-AJ** | Durée de tolérance des versions de client PWA | Politique | Sprint Chauffeur |
| **Q-AK** | Version de PHP réellement exécutée en production, et matrice à tester | **Vérification** | Q1 + mesure intermédiaire posée |
| **Q-AL** | Fréquence des exercices de restauration et de rotation | Politique (**SEC1**) | — |

### 22.2 Les cinq questions les plus urgentes, telles que le corpus les qualifie

| Réf | Qualification portée par le corpus |
|---|---|
| **Q-N** | « La plus urgente » *(ADR-004 §17)* — « si une relation dédiée ne respectait pas les policies sous-jacentes, tout le §5 s'effondrerait » |
| **Q1** | « Toujours bloquante pour le rate-limit fiable » *(ADR-012 §23)* — quatre points d'exploitation |
| **Q-AH**, **Q-AI** | « Des vérifications, pas des arbitrages — et toutes deux portent sur des dispositifs censés protéger la production **aujourd'hui** » *(ADR-013 §21)* |
| **Q-AA** | « La plus lourde […] elle conditionne la conformité » *(ADR-009 §25)* |

### 22.3 Questions tranchées au cours du corpus — vingt-quatre

Q-B · Q-C · Q-D · Q-E · Q-F · Q-G · Q-H · Q-I · Q-K · Q-L · Q-M · Q-R · Q-V · Q-W · Q-X · Q-Y · Q-Z · Q-AB · Q-AD · Q-AE, ainsi que Q2 à Q7 posées en cadrage à l'ADR-001.

### 22.4 Contradictions apparentes entre ADR

**Aucune contradiction de fond n'a été identifiée entre les quinze ADR.**

Les trois divergences détectées sont des **corrections explicitement assumées** par une ADR postérieure, et non des contradictions non résolues — voir §20.4. Chacune est documentée dans l'ADR correctrice et dans son historique de consolidation.

Les anomalies restantes relèvent de la **forme documentaire** — collisions de symboles, référence à un document absent — et sont recensées au §25. **Aucune n'est arbitrée par le présent document.**

---

## 23. Décisions irréversibles

### 23.1 Position générale du corpus

**Les quinze ADR déclarent, chacune dans sa section « Réversibilité », qu'aucune décision irréversible n'est prise.** C'est une propriété recherchée du principe directeur P6.

### 23.2 Les six points réellement irréversibles ou à coût durable

Le corpus identifie néanmoins six situations où le retour arrière est impossible ou coûteux. **Elles ne portent pas sur des choix d'architecture, mais sur des effets.**

| # | Point | Nature | Source |
|---|---|---|---|
| **1** | **Une donnée purgée ne revient pas** | Effet irréversible — **d'où l'exigence de tester chaque purge en staging** | ADR-011 §17 |
| **2** | **Une donnée perdue faute de sauvegarde éprouvée** | Effet irréversible — **la seule chose qui ne se répare pas** | ADR-013 §20 |
| **3** | **La confiance accordée à une chaîne qui mentait** | Irréversible — **les livraisons passées ne peuvent pas être revérifiées rétroactivement** | ADR-015 §21 |
| **4** | **Retirer un contrat d'événement consommé par un autre runtime** | Coût **élevé** — le seul engagement réellement durable, d'où le versionnement | ADR-006 §20, ADR-007 §21 |
| **5** | **Retirer le journal d'événements après qu'un module externe en dépend** | Coût **élevé** | ADR-007 §21 |
| **6** | **Le schéma de données ne revient jamais en arrière** | Par construction — **le code revient toujours, les données jamais** | ADR-013 §10, ADR-014 §5 |

### 23.3 Les propriétés structurelles à ne jamais compromettre

*Ces propriétés ne sont pas des décisions à défaire, mais des garanties acquises que le corpus demande de préserver.*

| Propriété | Formulation | Source |
|---|---|---|
| **Le chemin du chauffeur ne dépend jamais de WordPress** | « Ce n'est pas un effet secondaire à préserver par vigilance : c'est une propriété à ne jamais compromettre par commodité » | ADR-013 §2 |
| **Le code peut toujours revenir en arrière** | Toute livraison qui l'en empêcherait est **refusée** | ADR-014 §5 |
| **`actors.id` est indépendant du fournisseur d'authentification** | Un changement de fournisseur n'invalide ni l'audit ni les affectations | ADR-002 §6, ADR-003 §17 |
| **Le domaine ignore le fournisseur de modèle** | Changer de fournisseur ne modifie ni outils, ni autorisations, ni événements (**IA1**) | ADR-009 §12.1 |
| **La réservation atomique rend la coexistence de workers sûre** | Décision de conception du Sprint 1, **antérieure au besoin** **[F]** | ADR-008 §4.2 |

---

## 24. Décisions réversibles

*Synthèse des sections « Réversibilité » des quinze ADR.*

### 24.1 Réversibilité à coût nul

Tables d'identité ajoutées (vides) · liaison WordPress ↔ acteur · écriture de l'acteur d'audit · ajout ou retrait d'un handler non utilisé · retrait d'un hook non consommé · désactivation du drapeau `ai_enabled` **[F]** · suspension d'un agent · alignement CORS · durcissement de `form-token` · réponse minimale de `health` · ajout ou retrait d'un indicateur d'observabilité · `actors` et `role_assignments` tant qu'inutilisés.

### 24.2 Réversibilité à coût faible

Journal d'événements + trigger d'émission *(`drop trigger`)* · garde-fou de transition en base · **premières policies RLS** *(`drop policy` — **WordPress n'est pas affecté**)* · ouverture du premier consommateur JWT · contrainte référentielle sur les colonnes existantes · rattachement d'un contact à un compte · idempotence en base *(retour aux transients)* · catégorie A par défaut · versionnement par module · **séparation des cycles — purement additive** · lien contact ↔ acteur par colonne · table de suivi des migrations · **format des références publiques** tant qu'aucune référence opaque n'est diffusée · retour à une version antérieure de configuration d'agent *(garanti par l'immuabilité)* · abaissement d'un niveau d'autonomie · retrait d'un outil · **changement de fournisseur de modèle** (**IA1**) · WordPress Auth *(l'acteur est déjà l'ancrage)* · lien magique ↔ mot de passe · **ouverture de l'inscription autonome — sans refonte** · MFA activable population par population · cron système *(retour à WP-Cron)* · `pg_cron` · garde-fou d'environnement *(configuration)* · retrait d'une façade JS · retrait d'une étape de la chaîne CI.

### 24.3 Réversibilité à coût moyen

Première Edge Function *(le consommateur concerné cesse de fonctionner, **le reste est intact**)* · changement de fournisseur d'authentification *(reliaison — **l'identifiant canonique ne change pas**)* · stockage de session en PWA *(bascule vers un mandataire)* · **refonte du mode d'accès au reçu** *(seul point à impact utilisateur direct)* · contrats par consommateur *(fusion ultérieure)* · retrait d'une interface implémentée · zonage *(il conditionne les projections)* · retrait de la passerelle IA · **migration d'un handler vers une Edge Function — réversible à tout moment, les deux workers pouvant coexister** **[F]**.

### 24.4 Points de vigilance sur la réversibilité

- **Contact d'exécution** : réversible en structure, **mais la donnée dupliquée doit être purgée** *(ADR-010 §24)*.
- **Attribution des rôles de base par classe d'agent** : coûteuse à défaire *(ADR-004 §15)*.
- **Séparation des relations commerciales et opérationnelles** : coûteuse à défaire, mais **l'ADR-010 §5 en a établi le coût réel comme faible**, l'évaluation initiale ayant été corrigée.

---

## 25. Références documentaires

### 25.1 Corpus normatif

| Document | Chemin |
|---|---|
| ADR-001 à ADR-015 | `docs/architecture/adr/ADR-0NN.md` |
| Cartographie des flux d'autorisation | `docs/architecture/cartographies/AUTHORIZATION_FLOWS.md` |

### 25.2 Documents de référence dérivés

| Document | Chemin | Objet |
|---|---|---|
| Matrice des invariants | [`MATRIX_INVARIANTS.md`](MATRIX_INVARIANTS.md) | Recensement exhaustif des invariants, par famille |
| Glossaire d'architecture | [`ARCHITECTURE_GLOSSARY.md`](ARCHITECTURE_GLOSSARY.md) | Terminologie unique du corpus |
| **Ce document** | `HORIZON_ARCHITECTURE_REFERENCE_v1.0.md` | Synthèse fidèle du corpus |

### 25.3 Document référencé mais absent du dépôt

> **Anomalie documentaire majeure — signalée, non corrigée.**

| Document | Référencé par | Porte |
|---|---|---|
| **Cartographie conceptuelle du modèle d'identité** | ADR-002 §12 · ADR-003 §1 et §18 · ADR-004 §1 et §16 | **Les invariants I1 à I19** de la famille *Identité et acteurs* |

Ce document est déclaré « validé au même titre que le corpus » par l'ADR-002 §12, et l'ADR-003 §1 en fait une dépendance formelle. **Il n'est pas présent dans `docs/architecture/`.**

**Conséquence** : les invariants **I1 à I19** sont **cités** dans onze endroits du corpus mais **formulés nulle part dans les documents disponibles**. Onze d'entre eux sont reconstituables par leurs citations ; huit ne le sont pas. Voir [`MATRIX_INVARIANTS.md`](MATRIX_INVARIANTS.md) §3.

### 25.4 Anomalies documentaires recensées

*Ces anomalies sont **signalées et non corrigées**, conformément au périmètre du présent document. Le détail complet, avec localisation ligne à ligne, figure dans [`MATRIX_INVARIANTS.md`](MATRIX_INVARIANTS.md) §16.*

| # | Anomalie | Gravité | Statut dans le corpus |
|---|---|---|---|
| **1** | **Cartographie conceptuelle du modèle d'identité absente** — I1 à I19 non formulés | **Majeure** — référence cassée | **Non signalée par le corpus** |
| **2** | **Collision R1/R2** — règles d'approbation IA *(ADR-009 §9)* vs règles d'alerte *(ADR-012 §7)* | Moyenne | **Non signalée par le corpus** |
| **3** | **Collision C1–C4 / C1–C6** — critères d'introduction des Edge Functions *(ADR-001 §15)* vs critères de sensibilité *(ADR-011 §2)* | Moyenne | **Non signalée par le corpus** |
| **4** | **Collision D1–D6** — défauts de l'audit *(ADR-015 §13)* vs invariants *Données* *(ADR-010 §20)* | Moyenne | **Signalée par ADR-015 §13, explicitement non résolue** — **renommage futur DEF1 à DEF6** |
| **5** | **Liste des familles d'invariants de l'ADR-001 §18 incomplète** — cite `(I, A, E, CT, G, D, T, S, O, IA, DPL, R, C)` : omet **SEC** et **⛔**, inclut **R** et **C** qui ne sont pas des familles d'invariants | Faible | **Non signalée par le corpus** |
| **6** | **Renvoi possiblement obsolète — ADR-001 §16** mentionne « ADR-013 » pour le CRM sous une numérotation où ADR-013 portait l'observabilité ; l'ADR-001 ne comporte **aucune note de réalignement**, contrairement aux ADR-002, 005, 008 et 011 | Faible | **Non signalée par le corpus** |
| **7** | **Huit invariants I jamais cités** — I3, I5, I7, I8, I9, I11, I13, I18 n'apparaissent nulle part dans le corpus disponible | Faible | Conséquence de l'anomalie 1 |
| **8** | **Dates de décision non consignées** — les quinze ADR portent « Date : non consignée dans la source » | Faible | **Signalée par chaque ADR** |
| **9** | **Numérotation de condition d'acceptation — ADR-005 §25** : la condition 3 est libellée « *(2 bis)* », vestige d'une insertion | Cosmétique | Documentée dans l'historique de consolidation |
| **10** | **Bloc « Clôture du corpus architectural » non intégré** — écarté de l'ADR-015 au motif qu'il constituait une référence globale d'architecture « dont la création n'est pas autorisée à ce stade » | Information | **Signalée par ADR-015** — **le présent document en est la réalisation autorisée** |

### 25.5 Renumérotations acquises du corpus

*Ces renumérotations sont **résolues** et documentées dans les historiques de consolidation. Elles sont rappelées ici pour la lecture des textes antérieurs.*

| Renumérotation | Motif | ADR |
|---|---|---|
| **I16 → IA1 · I17 → IA2 · I18 → IA3 · I19 → IA4 · I20 → IA5** | Collision avec I16–I19 de la famille *Identité* | ADR-009 §20 |
| **S1 → SEC1 · S2 → SEC2 · S3 → SEC3 · S4 → SEC4 · S5 → SEC5** | Collision avec les règles S1–S7 de l'ADR-006 §11 | ADR-011 §13 |
| **E11 → CT1 · E12 → CT2 · E13 → CT3 · E14 → CT4 · E15 → CT5** | Surcharge de la famille E | ADR-013 §16 |
| **ADR-013 (observabilité) → ADR-012** | La stratégie de migration a été absorbée par l'ADR-010 §15–16 ; **aucune ADR distincte « Stratégie de migration » n'est créée** | ADR-012, note finale |

**Formulations strictement inchangées dans tous les cas.** Les familles **E1 à E10**, **I1 à I19** et les règles **S1 à S7** n'ont subi aucune modification.

### 25.6 Historique du corpus

| Étape | Contenu |
|---|---|
| **Base d'audit** | Dépôt à `d59409b` — v1.0.1, plugin 0.8.1, thème 0.2.0, 10 tables publiques, RLS active 10/10 sans policy **[F]** |
| **Rédaction** | ADR-001 à ADR-015, dans l'ordre de dépendance, chacune adossée aux faits de l'audit |
| **Documents transverses** | Cartographie conceptuelle du modèle d'identité *(I1–I19)* · Cartographie des flux d'autorisation *(A1–A16, ⛔1–⛔6)* |
| **Consolidation** | Chaque ADR a intégré ses ajustements validés ; chaque historique de consolidation en porte la trace ligne par ligne |
| **Arbitrages de collision** | Trois renumérotations résolues *(IA, SEC, CT)* ; une collision signalée non résolue *(D)* |
| **Gel** | **Architecture Horizon v1.0 — GELÉE**. Les quinze ADR sont ACCEPTÉES, la Cartographie des flux d'autorisation est VALIDÉE |
| **Clôture** | ADR-015 clôt le corpus architectural |
| **Présente référence** | Synthèse fidèle, sans décision nouvelle |

---

## Fin du document

> **Rappel** : ce document est une synthèse. **En cas de divergence, l'ADR fait foi.** Aucune décision d'architecture n'y est créée, modifiée ou arbitrée.
