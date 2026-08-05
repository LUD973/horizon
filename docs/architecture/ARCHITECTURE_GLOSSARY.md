# Glossaire d'architecture — Horizon v1.0

- **Document** : Glossaire unique du corpus architectural Horizon
- **Nature** : document de **terminologie** — **n'est pas une ADR**
- **Statut** : RÉFÉRENCE
- **Corpus de référence** : Architecture Horizon v1.0 — **GELÉE**
- **Référence de dépôt** : v1.0.1 / `d59409b8257f5a725828e281884a1c92e40da7a6`
- **Document parent** : [`HORIZON_ARCHITECTURE_REFERENCE_v1.0.md`](HORIZON_ARCHITECTURE_REFERENCE_v1.0.md)

---

## Avertissement

> **Ce glossaire définit la terminologie employée par le corpus. Il ne crée aucune décision et n'étend aucune définition au-delà de ce que les ADR énoncent.**

**Structure de chaque entrée** : définition · ADR de référence · synonymes acceptés · **termes déconseillés**, avec le motif.

**Sur les termes déconseillés** : un terme est déconseillé lorsque son emploi crée une confusion que le corpus a explicitement cherché à éviter. Les motifs sont toujours issus du corpus, jamais d'une préférence de rédaction.

---

## A

### Acteur

**Définition** — Abstraction commune d'attribution et d'audit, désignant tout ce qui agit dans Horizon : humain, service ou agent IA. Porte un identifiant, une **nature**, une référence **optionnelle** à un compte de connexion, un libellé d'affichage, un statut et des horodatages. **Aucun attribut métier.**

**Identifiant canonique** : `actors.id`, `uuid` généré par Horizon, **indépendant du fournisseur d'authentification et de WordPress**.

> **Se connecter est une capacité optionnelle d'un acteur, non sa définition.**

**ADR de référence** — ADR-002 §6 *(décision)* · ADR-002 §3 *(les sept concepts)*

**Synonymes acceptés** — *acteur canonique* · *identité canonique*

**Termes déconseillés**
- ❌ **« utilisateur »** au sens d'acteur — l'ADR-002 §3 distingue explicitement l'**identité de connexion** de l'**acteur d'audit** : un service agit sans jamais se connecter, un contact existe sans compte.
- ❌ **« compte »** au sens d'acteur — un compte est un moyen d'authentification ; l'acteur est le sujet d'attribution.

---

### Acteur de service

**Définition** — Acteur de nature « service », dédié à **un usage** et jamais partagé entre usages. Il ne s'authentifie jamais comme un humain. **Il est hors du système de policies** : il accède par `service_role`, qui contourne RLS par construction.

**ADR de référence** — ADR-002 §7 · ADR-003 §9 · ADR-004 §10 · Invariants **I14**, **A13**, **I17**

**Synonymes acceptés** — *compte de service* *(au sens d'acteur, non de compte de connexion)*

**Termes déconseillés**
- ❌ **« compte de service » au sens d'un compte connectable** — l'invariant **I1** interdit qu'un service dispose d'un compte humain connectable.
- ❌ **« acteur générique »** — ADR-003 §9 et ADR-008 §9 : **jamais d'acteur de service générique partagé entre usages**.

---

### Action sensible

**Définition** — Action satisfaisant au moins un des six critères **C1 à C6** de l'ADR-011 §2 : modifie des droits ou une identité · produit un effet externe irréversible · expose ou détruit des données personnelles · modifie une configuration de sécurité ou d'agent · déroge à un processus normal · engage financièrement.

**Deux propriétés indissociables** : elle exige un **acteur humain résolu** (**A5**, **⛔3**), et sa **qualification appartient exclusivement au runtime** (**A6**).

> **La règle prime sur la liste** : une action nouvelle est qualifiée par les critères, sans attendre une mise à jour d'inventaire.

**ADR de référence** — ADR-011 §2 *(critères)* · ADR-004 §12 et §12 bis *(qualification)*

**Synonymes acceptés** — aucun

**Termes déconseillés**
- ❌ **« action critique »** — non employé par le corpus ; « critique » y qualifie une gravité de risque, non une catégorie d'action.
- ❌ **« action privilégiée »** — « privilégié » qualifie une **fonction** ou une **surface**, pas une action.

---

### Affectation

**Définition** — Lien entre une organisation *(ou, par désignation nominative, une personne)* et une prestation. **C'est le vecteur d'accès du chauffeur et du partenaire.** Elle porte trois états : **actif**, **grâce** *(lecture seule)*, **clos** *(aucun accès)*.

> **Le rôle seul n'ouvre rien ; l'appartenance organisationnelle seule n'ouvre rien. L'affectation ouvre.**

**ADR de référence** — ADR-002 §3 *(axe orthogonal)* · ADR-004 §3.1 et §3.3 · Invariants **A2**, **A3**, **A4**, **⛔4**

**Synonymes acceptés** — aucun

**Termes déconseillés**
- ❌ **« assignation »** — le corpus emploie exclusivement « affectation ». La colonne existante `assigned_to` désigne, elle, le **responsable commercial interne** *(ADR-010 §5)* — un autre concept.

---

### Agrégat

**Définition** — **Frontière de cohérence métier** : à l'intérieur, les invariants sont garantis dans la transaction ; à l'extérieur, la cohérence est différée par événement.

> **Un agrégat n'est pas une table** (**D4**). Il peut se composer d'une table principale, de relations dépendantes, de valeurs embarquées, de projections ou d'une configuration versionnée.

**Neuf agrégats** : Acteur · Contact · Organisation · Demande · Mission · Communication · Journal · Audit · Configuration d'agent.

**ADR de référence** — ADR-010 §4 et §4.1

**Synonymes acceptés** — *frontière de cohérence*

**Termes déconseillés**
- ❌ **« entité »** au sens d'agrégat — une entité peut n'être qu'une relation dépendante à l'intérieur d'un agrégat.
- ❌ **« table »** au sens d'agrégat — expressément interdit par **D4**.

---

### Approbation

**Définition** — Validation humaine d'une action IA sensible. Régie par trois règles *(ADR-009 §9)* :

| Règle | Énoncé |
|---|---|
| **R1** | On ne peut approuver que ce que l'on pourrait faire soi-même |
| **R2** | L'approbation est elle-même une action sensible |
| **R3** | Une approbation expire |

**ADR de référence** — ADR-009 §9 · ADR-011 §3 *(Q-AB — durées)*

**Synonymes acceptés** — *validation humaine* · *human-in-the-loop* *(employé par le plan d'implémentation)*

**Termes déconseillés**
- ⚠️ **« R1 », « R2 » sans qualification** — **collision de symboles** avec les règles d'alerte **R1** et **R2** de l'ADR-012 §7. Toujours préciser l'ADR d'origine.

---

### Audit métier

**Définition** — Journal d'**imputabilité** à valeur probante. Écrit **dans la même transaction lorsque l'imputabilité de l'écriture l'exige**. Rétention longue. Accès interne restreint — **sa lecture en masse est une action sensible** (critère C3).

**Il n'est jamais échantillonné.** **Aucun humain n'y écrit directement** (**⛔6**).

**ADR de référence** — ADR-012 §2, §4, §5 · Cartographie §7 bis · Invariant **A16**

**Synonymes acceptés** — *journal d'audit*

**Termes déconseillés**
- ❌ **« log »** au sens d'audit — le corpus distingue **quatre journaux** aux garanties différentes *(ADR-012 §2)*. Confondre audit et journal technique est explicitement interdit.
- ❌ **« historique »** au sens d'audit — l'**histoire** est portée par le **journal d'événements** *(ADR-010 §11)*, pas par l'audit.

---

## B

### Battement de l'ordonnanceur

**Définition** — Signal périodique attestant que l'ordonnanceur fonctionne. **Son absence doit alerter.**

> **C'est le seul défaut qui ne produit aucune erreur** : si l'ordonnanceur s'arrête, rien n'échoue — la file grossit simplement, en silence.

**ADR de référence** — ADR-008 §9 · ADR-012 §7 *(règle R1 : l'absence de signal doit alerter)* · ADR-013 §8

**Synonymes acceptés** — *heartbeat*

**Termes déconseillés** — aucun

---

## C

### Cartographie

**Définition** — Document **transverse** du corpus, **qui n'est pas une ADR**. Deux cartographies existent :

| Document | Porte | État |
|---|---|---|
| **Cartographie des flux d'autorisation** | **A1 à A16** et **⛔1 à ⛔6** | ✅ Présente au dépôt |
| **Cartographie conceptuelle du modèle d'identité** | **I1 à I19** | ❌ **Absente du dépôt** |

**ADR de référence** — Cartographie §1 *(nature)* · ADR-002 §12 · ADR-004 §16

**Synonymes acceptés** — *document transverse*

**Termes déconseillés**
- ❌ **« ADR »** appliqué à une cartographie — le document lui-même précise : « n'est pas une ADR ».

---

### Commande

**Définition** — L'un des **quatre contrats d'intégration** (**G8**). Une commande est une **intention**, à l'impératif, adressée à **un** destinataire, **qui peut échouer**, et dont **la clé d'idempotence est obligatoire** lorsqu'elle produit des effets multiples.

**Règle de tri** *(ADR-004 §8)* : **si une écriture engage un invariant qui dépasse la ligne écrite, c'est une commande. Sinon, l'écriture directe est acceptable.**

**ADR de référence** — ADR-006 §8 · ADR-004 §8 et §8 bis · ADR-007 §3.2

**Synonymes acceptés** — *commande métier*

**Termes déconseillés**
- ❌ **« événement »** au sens de commande — **un événement n'est jamais une commande implicite** *(ADR-007 §3.2)* : il constate, il n'ordonne pas.

---

### Contact d'exécution

**Définition** — **Instantané métier figé** des seules informations identifiantes nécessaires à l'exécution d'une mission, porté par la mission elle-même. **Jamais resynchronisé automatiquement** avec la fiche CRM (**D3**).

**Il existe pour que l'exécutant n'accède jamais à `contacts`** — le problème d'autorisation devient un problème de modélisation, et disparaît.

**Contrepartie obligatoire** : rétention alignée sur le droit d'accès — **purgé à la fin du délai de grâce** *(Q-AE tranchée)*.

**ADR de référence** — ADR-010 §6 et §6.1 · ADR-011 §3 *(rétention)*

**Synonymes acceptés** — *instantané d'exécution*

**Termes déconseillés**
- ❌ **« copie technique »** — l'ADR-010 §6.1 le récuse explicitement : « ce n'est pas une copie technique : c'est un **instantané métier des informations effectivement communiquées** ».
- ❌ **« cache »** — un cache se resynchronise ; le contact d'exécution est figé par décision.

---

### Contexte d'accès

**Définition** — Dimension explicite d'autorisation, distincte du rôle. **Droits effectifs = rôles relus en base ∩ rôles admis dans le contexte.**

> **« Le contexte déclaré dans le jeton constitue une intention d'accès. Il doit être vérifié côté serveur. »**

Le contexte doit toujours **restreindre**, **jamais élargir** (**A1**). Absent ou incompatible sur une surface qui l'exige ⇒ **refus**.

**Le mécanisme physique n'est pas décidé** — **Q-O ouverte**.

**ADR de référence** — ADR-004 §3.2 · Cartographie §2 *(étape ⑤)*

**Synonymes acceptés** — *contexte*

**Termes déconseillés**
- ❌ **« scope »** — non employé par le corpus, et porteur d'une confusion avec la portée d'un rôle.
- ❌ **« revendication de contexte » employée comme preuve** — c'est une **intention**, pas une preuve.

---

### Corrélation *(identifiant de)*

**Définition** — Identifiant créé **au point d'entrée** et propagé à l'audit, aux événements, à la file d'envoi et aux journaux techniques. **Jamais régénéré en aval.**

> **Un webhook produit une commande, qui produit un événement, qui produit une livraison, qui appelle un fournisseur : quatre requêtes, une seule corrélation.**

**Il existe même pour les parcours anonymes** — c'est ce qui distingue « acteur inconnu » de « opération introuvable ».

**ADR de référence** — ADR-012 §3 et §3.1 · ADR-007 §9 · ADR-002 §7 bis · Invariant **O1**

**Synonymes acceptés** — `correlation_id`

**Termes déconseillés**
- ❌ **« identifiant de requête »** comme synonyme — **c'est la distinction qui manque le plus souvent** *(ADR-012 §3)*. Un `request_id` est **nouveau à chaque saut** ; un `correlation_id` couvre **le flux entier**.

---

## D

### Drapeau de fonctionnalité

**Définition** — Mécanisme d'**activation**, jamais d'autorisation (**E8**).

> **Un drapeau désactivé rend une fonctionnalité absente, jamais « présente mais interdite ». Si la seule chose qui empêche un accès est un drapeau, l'autorisation est manquante.**

**Défaut sûr** : absent, illisible ou indéterminé ⇒ **désactivé**.

**Deux types à distinguer** *(ADR-014 §6)* :

| | **Drapeau de déploiement** | **Option produit** |
|---|---|---|
| Durée de vie | **Temporaire — il doit mourir** | Permanente |
| Exemple | Activation d'un handler, d'une policy | `concierge_enabled` **[F]** |

**ADR de référence** — ADR-008 §11.1 · ADR-014 §6 et §6.1 · Invariants **E8**, **DPL2**

**Synonymes acceptés** — *feature flag*

**Termes déconseillés**
- ❌ **« interrupteur de sécurité »** — expressément interdit par **E8**.
- ❌ **« kill switch » au sens d'autorisation** — le drapeau `ai_enabled` est un mécanisme de **disponibilité**, jamais d'autorisation *(ADR-009 §16)*.

---

## E

### Edge Function

**Définition** — Second runtime applicatif, **limité et contrôlé**, jamais un second cœur applicatif. Introduit uniquement lorsqu'un des critères **C1 à C4** de l'ADR-001 §15 est rempli.

**Deux catégories** *(Cartographie §3b)* :

| Catégorie | Identité | `service_role` | RLS |
|---|---|---|---|
| **A — sous JWT utilisateur** | Celle de l'appelant | **non** | **oui — reste soumise** |
| **B — privilégiée** | Appelant ou service | **oui, sous conditions** | contournée |

**La catégorie A est le défaut ; la catégorie B est une exception justifiée cas par cas** (**A15**).

**ADR de référence** — ADR-001 §8.5 et §15 · ADR-005 §9 · Cartographie §3b

**Synonymes acceptés** — *fonction Edge* · *second runtime*

**Termes déconseillés**
- ❌ **« microservice »** — le corpus écarte explicitement les microservices prématurés (principe directeur P7).
- ❌ **« backend mobile »** — ADR-001 §8.3 : WordPress n'est **jamais** un backend mobile, et les Edge Functions ne sont pas un second cœur applicatif.

---

### Événement métier

**Définition** — **Fait accompli, immuable, au passé.** Il ne peut ni échouer, ni être annulé, ni être modifié. Une erreur se corrige par un **nouvel événement compensatoire**, jamais par la réécriture du précédent.

**Charge utile minimale**, soumise **aux mêmes règles de confidentialité que les contrats API** (**⛔1**) — **sans exception de destinataire**.

**Émis dans la même transaction que le changement d'état.**

**ADR de référence** — ADR-007 §4, §4.1, §5 · ADR-006 §8 · Invariants **E1**, **E2**

**Synonymes acceptés** — *fait* · *événement*

**Termes déconseillés**
- ❌ **« événement d'intégration »** — **expressément non introduit par le corpus**. La note terminologique de l'ADR-007 précise : « Il n'emploie pas le terme "événement d'intégration", qui n'a donc pas été introduit. »
- ❌ **« message »** au sens d'événement — un message est un élément de la file de communication ; l'événement est un fait.
- ❌ **« commande »** au sens d'événement — voir *Commande*.

---

### Event sourcing *(non retenu)*

**Définition** — Patron consistant à reconstruire l'état à partir des événements. **Horizon n'est pas événementiellement sourcé.**

> Les tables d'état restent la **source de vérité de l'état**. Le journal d'événements est un **journal de faits**, pas l'état lui-même.

**ADR de référence** — ADR-007 §12

**Synonymes acceptés** — *événementiellement sourcé*

**Termes déconseillés** — n/a *(le concept est nommé pour être écarté)*

---

## F

### Fiche métier

**Définition** — Ensemble des caractéristiques propres à une population : un chauffeur a un permis, un client non. **Elle n'est créée que lorsque sa population est effectivement livrée.**

**Portée par la population, jamais par l'acteur.**

**ADR de référence** — ADR-002 §3, §7, §8.9 *(garde-fou 3)*

**Synonymes acceptés** — aucun

**Termes déconseillés**
- ❌ **« profil métier »** — terme du texte source, **remplacé par « fiche métier »** lors de la consolidation de l'ADR-002 *(historique, ajustement 5)*.

---

### File de sortie

**Définition** — Table de suivi des livraisons, distincte du journal d'événements. Elle garantit qu'un effet est produit **au moins une fois**. **Elle n'est pas immuable** : elle porte des statuts et des tentatives.

**Réservation atomique** avant traitement — mécanisme déjà en production **[F]**.

**ADR de référence** — ADR-007 §5 · ADR-008 §4.2

**Synonymes acceptés** — *outbox* · *outbox transactionnelle*

**Termes déconseillés**
- ❌ **« journal d'événements »** comme synonyme — **deux notions à ne pas confondre** *(ADR-007 §5)* : le journal est la source de vérité de ce qui s'est passé ; la file est l'état de livraison par handler.
- ❌ **« bus de messages »** / **« courtier »** — explicitement écartés *(ADR-007 §2)*.

---

## H

### Handler

**Définition** — **Unité de réaction indépendante** à un événement. Il ne connaît ni l'émetteur, ni les autres handlers, ni leur ordre. **Tout handler est idempotent.**

> **Un handler ne déclenche jamais directement un autre handler** (**E3**). La chaîne est : `commande → nouvel événement → nouveau handler`.

**Aucune logique métier cachée dans un handler** : s'il doit décider, il émet une commande.

**ADR de référence** — ADR-007 §6 et §6.1 · Invariants **E3**, **E10**

**Synonymes acceptés** — *réaction*

**Termes déconseillés**
- ❌ **« listener »** — non employé par le corpus.
- ❌ **« worker »** comme synonyme — voir *Worker* : le worker **exécute** des handlers, il n'en est pas un.

---

### Hook

**Définition** — Point d'extension WordPress *(`apply_filters` / `do_action`)*, **niveau 3 des contrats d'extension**, autorisé **uniquement pour des adaptations locales non critiques** et **interdit sur dix zones de sécurité** (**G5**).

**Trois niveaux** *(ADR-006 §7.2)* : **1 — interne** *(aucune garantie, défaut)* · **2 — documenté** · **3 — contractuel**.

> **Un hook non déclaré est de niveau 1 par défaut. La promotion vers le niveau 3 est une décision, jamais une conséquence de l'usage.**

**ADR de référence** — ADR-006 §5, §7.1, §7.2 · Invariants **G4**, **G5**, **S5**

**Synonymes acceptés** — *filtre* · *action WordPress*

**Termes déconseillés**
- ❌ **« événement »** au sens de hook — un hook **ne franchit aucun runtime**, n'est pas durable, pas rejouable *(ADR-006 §8)*. « Une Edge Function, un worker ou une passerelle IA ne peuvent pas consommer un hook PHP. »
- ❌ **« API d'extension »** au sens général — le corpus distingue trois niveaux de contrat de criticité différente.

---

## I

### Idempotence

**Définition** — Propriété d'une opération dont la répétition ne produit pas d'effet supplémentaire.

**Portée** : **(principal d'idempotence, type de commande, clé)**.

> **« La clé d'idempotence est créée par l'initiateur fiable de l'intention et reste stable lors de tous les rejeux de cette même intention. »**

> **L'idempotence n'est pas une responsabilité du client seul : elle est garantie côté serveur** (**A10**).

**Trois niveaux coexistent** : commande *(clé)* · livraison *(événement, handler)* · webhook *(identifiant fournisseur)*.

**ADR de référence** — ADR-004 §8 bis · ADR-005 §15 · ADR-007 §11 · Invariant **A10**

**Synonymes acceptés** — aucun

**Termes déconseillés**
- ❌ **« exactement une fois »** — **impossible à garantir dans un système distribué** *(ADR-007 §7)*. Le corpus retient explicitement **« au moins une fois »**. Prétendre offrir l'exactement-une-fois conduirait à des handlers non idempotents.

---

### Invariant transversal

**Définition** — Règle qui engage **simultanément deux agrégats** et ne peut appartenir en propre à aucun des deux. **Chaque invariant transversal est garanti à un lieu unique**, indiqué explicitement.

**Six invariants : T1 à T6.**

> **Règle générale : un invariant qui engage deux agrégats est garanti en base ; un invariant qui déclenche une suite appartient au domaine.**

**ADR de référence** — ADR-001 §8.4 *(exigence)* · ADR-010 §7 *(recensement)*

**Synonymes acceptés** — aucun

**Termes déconseillés**
- ❌ **« règle métier partagée »** — l'objectif du corpus est précisément d'éviter qu'une règle vive à deux endroits.

---

## J

### Journal d'événements

**Définition** — Table portant les faits accomplis. **Monotone** (**E2**) : ni modification, ni remplacement, ni réordonnancement, ni suppression individuelle. Seuls l'ajout, l'archivage et la purge documentée sont admis, **sur des ensembles définis par une politique écrite**.

> **C'est l'histoire** *(ADR-010 §11)*. Un seul historique est tenu — `audit_logs.old_values` n'est renseigné que pour les actions sensibles.

**Aucun consommateur externe ne le lit** *(ADR-007 §13)*.

**ADR de référence** — ADR-007 §4, §4.1, §13 · ADR-010 §11 · Invariant **E2**

**Synonymes acceptés** — *journal des faits*

**Termes déconseillés**
- ❌ **« file de sortie »** comme synonyme — voir *File de sortie*.
- ❌ **« audit »** comme synonyme — quatre journaux distincts *(ADR-012 §2)*.

---

### Journal de sécurité

**Définition** — Quatrième journal, **à mécanisme de livraison séparé et à garantie propre**. **Append-only** (**O3**). Rétention longue, accès très restreint.

**Contenu propre** : échecs d'authentification · refus d'autorisation · refus de la liste d'outils IA · événements de facteur d'authentification · usage d'un compte d'urgence · rotation de secret · déploiement ou retrait de policy · export et purge · envoi bloqué par l'environnement.

> **Un journal de sécurité modifiable ne prouve rien.** Une entrée erronée se corrige par une **entrée rectificative**.

**Son support n'est pas décidé** — **Q-AG ouverte**.

**ADR de référence** — ADR-012 §2, §2.1 · Cartographie §7 bis · Invariant **O3**

**Synonymes acceptés** — aucun

**Termes déconseillés**
- ❌ **« log de sécurité »** au sens de catégorie du journal technique — le corpus en fait un **journal propre**, précisément parce que ses événements « intéressent la sécurité même quand tout fonctionne normalement ».

---

### Journal technique

**Définition** — Journal de **diagnostic et de mesure**, produit **hors transaction**. Rétention courte. **Aucune donnée personnelle inutile.** **Sans valeur probante.**

> **La journalisation technique ne bloque jamais l'opération métier** (**A16**).

**ADR de référence** — ADR-012 §2, §5 · Cartographie §7 bis · Invariant **A16**

**Synonymes acceptés** — *log technique* · *log de diagnostic*

**Termes déconseillés**
- ❌ **« audit »** — confusion explicitement interdite *(ADR-012 §15)* : « traiter un journal technique comme un audit probant » figure parmi les interdits du modèle d'observabilité.

---

## L

### Lecture sensible

**Définition** — Lecture produisant, **en plus de la trace technique**, un **audit métier explicite non échantillonnable**.

**Périmètre** : export · consultation de données personnelles étendues · accès financier · accès administratif transversal · téléchargement de documents sensibles · **accès exceptionnel ou dérogatoire** · consultation dans le cadre d'un incident · accès à une configuration de sécurité.

**La classification est faite par le runtime**, jamais déclarée par l'appelant.

**ADR de référence** — ADR-005 §5.1 · ADR-012 §4

**Synonymes acceptés** — aucun

**Termes déconseillés** — aucun

---

## M

### Mission

**Définition** — Agrégat du **cycle opérationnel**, porté par une relation `missions` **distincte de `enquiries`**. Elle porte le trajet, l'horaire, les passagers, les contraintes d'exécution, les affectations et le contact d'exécution.

**Argument décisif de la séparation** *(ADR-010 §5)* : **⛔1** interdit toute donnée commerciale au chauffeur, et **RLS filtre des lignes, pas des colonnes** — l'interdiction serait inapplicable si la mission vivait dans `enquiries`.

**ADR de référence** — ADR-010 §5 · ADR-001 §8.4

**Synonymes acceptés** — aucun

**Termes déconseillés**
- ❌ **« demande au statut mission »** — la valeur `status = 'mission'` **[F]** **cesse d'être employée** *(ADR-010 §5)*.
- ❌ **« prestation »** comme strict synonyme — « prestation » est employé au sens contractuel *(ce que FCP confie à une organisation)* ; « mission » au sens opérationnel.

---

## O

### Outil *(IA)*

**Définition** — Contrat fonctionnel exposé à un agent par la passerelle. **Un outil = une intention.** Jamais d'outil générique.

**Interdits absolus** : requête SQL arbitraire · appel HTTP arbitraire · accès fichier arbitraire · exécution de code.

> **« Les outils constituent les véritables contrats fonctionnels. Le modèle ne fait que décider quand les appeler. »** (**IA3**)

**Un outil de lecture s'exécute ; un outil d'écriture produit une proposition.**

**ADR de référence** — ADR-009 §7, §7.1 · Invariant **IA3**

**Synonymes acceptés** — *contrat d'outil*

**Termes déconseillés**
- ❌ **« fonction »** au sens d'outil — confusion avec les Edge Functions.
- ❌ **« outil paramétrable générique »** — l'ADR-009 §7 : « un seul outil générique viderait la liste d'autorisation de son sens ».

---

## P

### Passerelle d'outils IA

**Définition** — **Surface unique de l'IA.** Aucun accès direct à la base, **jamais de `service_role`** (**⛔2**). Elle exécute **onze contrôles** à chaque appel et obtient un **rôle de base restreint par classe d'agent**, soumis aux policies.

> **La passerelle est le seul lieu où la capacité d'un agent est appliquée.** Sa complexité est un risque de sécurité direct.

**ADR de référence** — ADR-009 §5 · ADR-004 §11 *(option B)* · ADR-005 §12

**Synonymes acceptés** — *passerelle* · *gateway d'outils*

**Termes déconseillés**
- ❌ **« proxy IA »** — non employé par le corpus, et suggère à tort un simple relais.

---

### Projection

**Définition** — Relation dédiée à un consommateur déterminé, servant à clarifier un **périmètre de confidentialité**.

> **« Une projection ne combine pas plusieurs zones sauf si ce croisement est explicitement justifié, minimal, documenté et protégé par un contrat d'accès dédié. »**

> **Une projection n'est jamais une frontière de sécurité** (**A8**).

**Les projections d'Horizon sont des projections de confidentialité, non de performance** *(ADR-007 §12)*.

**ADR de référence** — ADR-010 §3.1, §13 · ADR-004 §5 · ADR-005 §3.1 · Invariants **A8**, **D1**, **D2**

**Synonymes acceptés** — *relation dédiée* · *vue dédiée*

**Termes déconseillés**
- ❌ **« vue de sécurité »** — expressément récusé : **A8** énonce qu'une relation dédiée n'est pas une frontière de sécurité.
- ❌ **« table de lecture »** au sens de dénormalisation — **aucune table de lecture dénormalisée n'est créée** tant qu'un besoin de performance réel n'est pas mesuré *(ADR-007 §12)*.

---

### Proposition *(IA)*

**Définition** — Sortie d'un outil d'écriture d'un agent. **Un agent ne produit que des propositions** ; l'exécution exige une approbation humaine, **revérifiée à l'exécution**.

Le triplet `AIActionProposed / AIActionApproved / AIActionExecuted` **est** le mécanisme de validation humaine, non un journal a posteriori.

**ADR de référence** — ADR-009 §4, §9 · ADR-007 §14 · ADR-004 §11

**Synonymes acceptés** — aucun

**Termes déconseillés**
- ❌ **« action IA »** employé pour une proposition non approuvée — le corpus distingue strictement proposition, approbation et exécution.

---

## R

### Réconciliation

**Définition** — Contrôle périodique d'écart entre l'état et les effets. **Exclusivement observationnel.**

> **« Une réconciliation ne modifie jamais l'état métier »** (**E4**).

**Elle peut** : détecter · mesurer · signaler · ouvrir une alerte · produire un rapport.
**Elle ne peut jamais** : créer ou modifier un événement · corriger une ligne · **relivrer automatiquement** · changer un statut · **déclencher une purge**.

**Fréquence** *(Q-X tranchée)* : quotidienne et hebdomadaire.

**ADR de référence** — ADR-007 §15, §15.1 · ADR-012 §12 · Invariant **E4**

**Synonymes acceptés** — aucun

**Termes déconseillés**
- ❌ **« correction automatique »** — expressément interdite par **E4**.
- ❌ **« auto-remédiation »** — même motif.

---

### Référence publique opaque

**Définition** — Identifiant exposé à un client, un chauffeur, un partenaire ou un tiers. **Non séquentielle, non dérivable.**

> **« Une référence publique ne doit pas permettre d'inférer le volume d'activité, la chronologie, le nombre de missions, l'identité d'un client ou la relation entre plusieurs dossiers. »** (**D5**)

**Trois niveaux d'identification à ne jamais confondre** : identifiant interne *(jamais exposé)* · référence métier interne *(interne uniquement)* · référence publique opaque.

**ADR de référence** — ADR-010 §10, §10.1 · Invariant **D5**

**Synonymes acceptés** — *jeton de possession* *(dans le cas du reçu)*

**Termes déconseillés**
- ❌ **« référence lisible »** au sens de référence exposée — l'ADR-010 §10.1 révise rétroactivement le format séquentiel existant **[F]** : **l'opacité est un invariant de sécurité, non une préférence de format**.

---

### Rejeu

**Définition** — **Relivrer un événement existant** à un handler. Rendu possible par l'idempotence.

> **Rejouer n'est pas ré-émettre.** On ne crée jamais un nouvel événement pour refaire quelque chose — cela falsifierait le journal des faits.

**Usages légitimes** : correction d'un handler défectueux · mise en service d'un nouveau handler sur l'historique · reprise après incident.

**ADR de référence** — ADR-007 §15

**Synonymes acceptés** — *relivraison*

**Termes déconseillés**
- ❌ **« ré-émission »** comme synonyme — distinction explicite et impérative.

---

### RLS *(Row Level Security)*

**Définition** — **Frontière de sécurité de référence pour tout accès non-WordPress.** Elle porte la **visibilité et les droits élémentaires par ligne**.

**Ce qu'elle ne porte jamais** : toute règle métier composite, tout enchaînement, toute condition temporelle complexe.

**Deux limites énoncées explicitement** :
1. **RLS agit au niveau de la ligne, pas de la colonne** — d'où la séparation obligatoire des zones au niveau des relations.
2. **RLS ne protège pas contre `service_role`**, qui la contourne par construction **[F]**.

**ADR de référence** — ADR-001 §7, §8 · ADR-004 §4, §9, §10 · Invariants **A7**, **A11**, **A12**

**Synonymes acceptés** — *sécurité au niveau ligne*

**Termes déconseillés**
- ❌ **« RLS protège tout »** — l'ADR-004 §10 : « **Énoncer clairement cette limite vaut mieux que de laisser croire que RLS couvre tout.** »

---

### Rôle

**Définition** — Axe d'autorisation **global par nature** : une personne *est* chauffeur, collaborateur, client. **Relu en base à chaque opération, jamais figé dans le jeton.**

**Une seule exception contextuelle** : le rôle d'administration **propre à une organisation partenaire**, qui **ne peut jamais produire un rôle global Horizon** (**⛔5**).

> **Le rôle seul n'ouvre aucune ligne de données.** La restriction vient du contexte, de l'appartenance et de l'affectation.

**ADR de référence** — ADR-004 §6 · ADR-002 §3 · Invariants **A2**, **⛔5**

**Synonymes acceptés** — aucun

**Termes déconseillés**
- ❌ **« rôle de base »** comme synonyme — un **rôle de base** est un rôle Postgres *(`anon` / `authenticated` / `service_role`, plus les rôles restreints d'agent)*, pas un rôle métier Horizon *(ADR-003 §9)*.
- ❌ **« permission »** — le corpus écarte explicitement toute **table générique de permissions** comme prématurée *(ADR-002 §8.9, garde-fou 2)*.

---

## S

### `service_role`

**Définition** — Rôle Postgres **contournant RLS par construction** **[F]**. Confiné aux runtimes serveur de confiance. **Jamais exposé au navigateur, aux applications ou aux agents IA** (principe directeur P4, **A7**, **S2**, **⛔2**).

**Trois leviers d'encadrement, aucun n'étant une policy** : **confinement** · **attribution** *(un acteur de service par usage)* · **rotation** *(protocole en dix étapes)*.

**Risque résiduel structurel acté** : `service_role` reste lisible par tout plugin WordPress **[F]**. **Aucune solution technique n'existe dans WordPress** ; seule la gouvernance des plugins y répond.

**ADR de référence** — ADR-001 §4 · ADR-003 §9, §9.1 · ADR-004 §10 · ADR-011 §16

**Synonymes acceptés** — *clé de service*

**Termes déconseillés**
- ❌ **« clé admin »** — non employé par le corpus.

---

### Surface

**Définition** — Point d'entrée du système. **Huit surfaces** *(ADR-005 §4)*, rattachées aux **quatre familles de chemins d'accès** *(Cartographie §1)*.

> **Une neuvième surface qui ne se rattacherait à aucune famille serait un signal d'alerte architectural.**

**ADR de référence** — ADR-005 §4 · Cartographie §1, §3a, §3b

**Synonymes acceptés** — *surface d'accès*

**Termes déconseillés** — aucun

---

## Z

### Zone de confidentialité

**Définition** — Régime de gouvernance d'une donnée, définissant son **régime d'accès** et sa **politique de conservation par défaut**. Quatre zones : **identifiante · commerciale · opérationnelle · technique**. **Toute relation appartient à une seule zone, déclarée.**

> **« Une zone décrit la responsabilité principale d'une donnée […]. Elle ne signifie pas que cette donnée ne peut jamais apparaître ailleurs. »** (**D1**)

> **« Ce qui est interdit n'est pas toute duplication, mais la traversée non maîtrisée d'une frontière de confidentialité. »** (**D2**)

**Pas de préfixe de zone dans les noms de relations** — une zone est appliquée par les privilèges, non par le nommage.

**ADR de référence** — ADR-010 §3, §3.1, §14 · Invariants **D1**, **D2**

**Synonymes acceptés** — *zone*

**Termes déconseillés**
- ❌ **« silo »** — expressément récusé par **D1** : « **Les zones ne sont pas des silos.** »
- ❌ **« classification de données »** au sens de niveau par colonne — l'ADR-011 §10 précise ne créer « **aucun niveau de sensibilité par colonne, ni taxonomie de données distincte des zones de l'ADR-010** ».

---

## W

### Worker

**Définition** — **Rôle**, non un runtime *(ADR-008 §3)*. Il exécute des handlers et traite la file de sortie. Hébergeable dans WordPress ou dans une Edge Function **selon le handler**.

> **« Un worker est totalement remplaçable »** (**E7**) — sans état métier durable, sans session, sans mémoire locale de reprise. **Toute reprise repose exclusivement sur PostgreSQL.**

**Deux workers concurrents sont sûrs par construction** grâce à la réservation atomique **[F]**.

**ADR de référence** — ADR-008 §3, §3.1, §4 · ADR-013 §9 · Invariants **E7**, **E10**

**Synonymes acceptés** — *traitement de file*

**Termes déconseillés**
- ❌ **« runtime »** au sens de worker — expressément récusé : « le worker n'est pas un runtime : c'est un rôle ».
- ❌ **« handler »** comme synonyme — voir *Handler*.

---

## Termes déconseillés — récapitulatif

| Terme déconseillé | À remplacer par | Motif, et source |
|---|---|---|
| **événement d'intégration** | *événement métier* | **Terme expressément non introduit par le corpus** — ADR-007, note terminologique |
| **exactement une fois** | *au moins une fois* | Impossible à garantir ; conduirait à des handlers non idempotents — ADR-007 §7 |
| **profil métier** | *fiche métier* | Remplacé lors de la consolidation — ADR-002, ajustement 5 |
| **silo** *(pour une zone)* | *régime de gouvernance* | **D1** — ADR-010 §3.1 |
| **vue de sécurité** | *projection* / *relation dédiée* | **A8** — une relation dédiée n'est pas une frontière de sécurité |
| **RLS protège tout** | *RLS ne protège pas contre `service_role`* | ADR-004 §10 |
| **rejouer = ré-émettre** | *rejouer ≠ ré-émettre* | ADR-007 §15 |
| **correction automatique** *(réconciliation)* | *rapport* / *signalement* | **E4** — ADR-007 §15.1 |
| **drapeau comme contrôle de sécurité** | *drapeau = activation* | **E8** — ADR-008 §11.1 |
| **hook = événement** | *deux mécanismes distincts* | ADR-006 §8 — un hook ne franchit aucun runtime |
| **acteur générique** | *un acteur de service par usage* | **I14** — ADR-003 §9 |
| **identifiant de requête = corrélation** | *deux identifiants distincts* | **O1** — ADR-012 §3 |
| **audit = log technique** | *quatre journaux distincts* | ADR-012 §2 et §15 |
| **sauvegarde comme historique** | *archive* | **SEC2** — ADR-011 §6.1 |
| **métrique comme source métier** | *agrégats, commandes, événements, audits* | **O2** — ADR-012 §8.1 |
| **microservice** | *architecture hybride incrémentale* | Principe directeur P7 — ADR-001 §4 |

---

## Termes à qualifier systématiquement — collisions de symboles

| Symbole | Deux emplois | Qualification requise |
|---|---|---|
| **C1 à C4 / C1 à C6** | Critères d'introduction des Edge Functions *(ADR-001 §15)* **vs** critères de sensibilité *(ADR-011 §2)* | **Toujours nommer l'ADR d'origine** |
| **R1 à R3 / R1 à R2** | Règles d'approbation IA *(ADR-009 §9)* **vs** règles d'alerte *(ADR-012 §7)* | **Toujours nommer l'ADR d'origine** |
| **D1 à D6** | Invariants *Données* *(ADR-010 §20)* **vs** défauts de l'audit technique *(ADR-015 §13)* | **Toujours nommer l'ADR d'origine** — renommage futur **DEF1 à DEF6** envisagé |
| **S1 à S7** | Règles de sécurité des extensions *(ADR-006 §11)* — **ne pas confondre avec SEC1 à SEC5** *(ADR-011 §13)*, qui portaient autrefois ces symboles | Employer **SEC** pour la famille sécurité et conservation |
| **I16 à I19** | Famille *Identité* — **ne pas confondre avec IA1 à IA5** *(ADR-009)*, qui portaient autrefois I16 à I20 | Employer **IA** pour la famille IA |
| **E11 à E15** | **Identifiants abandonnés** — renommés **CT1 à CT5** *(ADR-013 §16)* | **Ne jamais employer E11 à E15** |

Voir [`MATRIX_INVARIANTS.md`](MATRIX_INVARIANTS.md) §15 et §16.

---

## Fin du document

> **Rappel** : ce glossaire reprend la terminologie du corpus. **En cas de divergence, l'ADR source fait foi.** Aucune définition n'y est étendue au-delà de ce que les ADR énoncent.
