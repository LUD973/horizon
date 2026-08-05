# Matrice des invariants — Architecture Horizon v1.0

- **Document** : Matrice unique des invariants du corpus architectural Horizon
- **Nature** : document de **recensement** — **n'est pas une ADR**
- **Statut** : RÉFÉRENCE
- **Corpus de référence** : Architecture Horizon v1.0 — **GELÉE**
- **Référence de dépôt** : v1.0.1 / `d59409b8257f5a725828e281884a1c92e40da7a6`
- **Document parent** : [`HORIZON_ARCHITECTURE_REFERENCE_v1.0.md`](HORIZON_ARCHITECTURE_REFERENCE_v1.0.md)

---

## Avertissement

> **Ce document recense. Il ne crée, ne renumérote et ne modifie aucun invariant.**

**Les formulations sont reprises fidèlement de leur ADR source.** Lorsqu'un invariant n'est pas formulé dans un document disponible, il est signalé comme tel — **aucune formulation n'est inventée**.

**Conventions** : **[F]** fait vérifié · **[R]** recommandation · **[H]** hypothèse.

---

## 1. Vue d'ensemble des familles

| Famille | Domaine | Identifiants | Nombre | Document source | Statut de la source |
|---|---|---|---|---|---|
| **A** | Autorisation | A1 → A16 | 16 | Cartographie des flux d'autorisation §7 | ✅ Présent |
| **⛔** | Interdictions certaines | ⛔1 → ⛔6 | 6 | Cartographie des flux d'autorisation §4 | ✅ Présent |
| **I** | Identité et acteurs | I1 → I19 | 19 | *Cartographie conceptuelle du modèle d'identité* | ❌ **ABSENT DU DÉPÔT** |
| **G** | Gouvernance de l'extensibilité | G1 → G8 | 8 | ADR-006 §16 | ✅ Présent |
| **S** | Sécurité des extensions | S1 → S7 | 7 | ADR-006 §11 | ✅ Présent |
| **E** | Événementiel et exécution | E1 → E10 | 10 | ADR-007 §17 *(E1–E5)* · ADR-008 §13 *(E6–E10)* | ✅ Présent |
| **IA** | Architecture IA | IA1 → IA5 | 5 | ADR-009 §20 | ✅ Présent |
| **D** | Données | D1 → D6 | 6 | ADR-010 §20 | ✅ Présent |
| **T** | Invariants transversaux | T1 → T6 | 6 | ADR-010 §7 | ✅ Présent |
| **SEC** | Sécurité et conservation | SEC1 → SEC5 | 5 | ADR-011 §13 | ✅ Présent |
| **O** | Observabilité | O1 → O5 | 5 | ADR-012 §18 | ✅ Présent |
| **CT** | Continuité | CT1 → CT5 | 5 | ADR-013 §16 | ✅ Présent |
| **DPL** | Déploiement | DPL1 → DPL5 | 5 | ADR-014 §15 | ✅ Présent |

**Total déclaré : 103 invariants.**
**Total formulé dans les documents disponibles : 84.**
**Écart : 19** — la famille **I**, dont le document source est absent du dépôt (§3).

**ADR ne créant aucun invariant** : ADR-001, ADR-002, ADR-003, ADR-004, ADR-005, ADR-015. Elles **appliquent** des invariants formulés ailleurs — l'ADR-015 en particulier a pour fonction de les rendre **vérifiables**.

---

## 2. Famille A — Autorisation

- **Source** : Cartographie des flux d'autorisation §7
- **Origine** : émerge de la mise en regard des ADR-001 à ADR-004
- **Statut** : VALIDÉE — **A14 et A15 sont l'apport propre de la cartographie**, non d'une ADR prise isolément

| # | Intitulé | ADR source | Rôle | Domaine protégé | Références croisées |
|---|---|---|---|---|---|
| **A1** | **Le contexte ne peut que restreindre**, jamais élargir — et il est vérifié côté serveur | Cartographie §7 · ADR-004 §3.2 | Empêche qu'un cumul de rôles ouvre des données hors du contexte d'accès | Étape ⑤ de la chaîne · cumul de rôles | **Q-I** *(tranchée)* · **Q-O** *(mécanisme, ouverte)* · Flux C, D, E |
| **A2** | Le rôle seul n'ouvre aucune mission partenaire | Cartographie §7 · ADR-002 §3 · ADR-004 §7 | Impose l'affectation comme vecteur d'accès | Orthogonalité rôle / organisation / affectation | **A3**, **A4** · **⛔1** · Flux E |
| **A3** | L'appartenance organisationnelle seule n'ouvre aucune prestation | Cartographie §7 · ADR-002 §7 | Empêche qu'appartenir suffise à voir | Contrainte Q5 | **A2**, **A4** · Flux E · **Q-H** *(tranchée)* |
| **A4** | L'affectation est nécessaire pour tout accès opérationnel | Cartographie §7 · ADR-004 §3.1 | Fait de l'affectation le prédicat central des policies | Accès chauffeur et partenaire | **⛔4** · Flux D, E · **Q-B**, **Q-M** *(tranchées)* |
| **A5** | Une action sensible exige un acteur humain résolu | Cartographie §7 · ADR-004 §12 | Ferme tout chemin d'action sensible par un service ou un agent | Imputabilité des actions sensibles | **⛔3** · **I16** · **SEC5** · ADR-011 §2 · Flux B, G |
| **A6** | **Un agent IA ne classe jamais lui-même une action** — la qualification appartient au runtime | Cartographie §7 · ADR-004 §12 bis | Empêche un agent de se déclarer non sensible | Qualification de sensibilité | **E5** · ADR-009 §5 étape 6 · Flux F, G |
| **A7** | `service_role` contourne RLS et doit rester confiné | Cartographie §7 · ADR-001 §4 (P4) · ADR-004 §10 | Énonce la limite réelle de RLS | Surfaces privilégiées | **S2** · **A15** · **SEC4** · ADR-003 §9 |
| **A8** | **Une relation dédiée n'est pas une frontière de sécurité** | Cartographie §7 · ADR-004 §5 | Empêche de confondre lisibilité du contrat et protection | Protection par colonne | **Q-N** *(ouverte, bloquante)* · Refus 15 · ADR-005 §8 |
| **A9** | **La résolution dynamique de l'acteur est obligatoire sur toute surface authentifiée** | Cartographie §7 · ADR-003 §8.1 | Rend la révocation immédiate effective partout | Étape ③ · révocation immédiate | **I6** · **I15** · ADR-005 §17 · ADR-009 §5 étape 2 |
| **A10** | Toute commande externe à effets multiples est idempotente, **garantie côté serveur** | Cartographie §7 · ADR-004 §8 bis | Absorbe les rejeux réseau et la synchronisation hors ligne | Flux D, E, H | ADR-005 §15 · ADR-007 §11 · **Q-P** *(ouverte)* |
| **A11** | Aucune policy formulée en négatif | Cartographie §7 · ADR-004 §13 | Maintient des droits additifs et explicites | Refus par défaut | **A12** · **E8** |
| **A12** | Aucune table livrée sans RLS et tests | Cartographie §7 · ADR-004 §13 | Fait d'une table sans policy une erreur de déploiement | Refus par défaut | **A11** · **E8** · ADR-015 §5 |
| **A13** | Toute action système nouvelle est attribuée à un acteur de service | Cartographie §7 · ADR-008 §9 | Rend l'audit des traitements automatiques exploitable | Flux I | **I14** · **I17** · ADR-002 §8.6 |
| **A14** | **Un agent IA n'a aucun accès à sa propre configuration**, ni en lecture ni en écriture | Cartographie §7 *(apport propre)* · ADR-009 §6 | Empêche un agent d'élargir sa propre liste d'outils | **⛔2** | **IA2** · ADR-009 §13 · ADR-015 §9 |
| **A15** | **Toute fonction privilégiée utilisant `service_role` doit revalider l'identité, résoudre l'acteur ou le service, appliquer l'autorisation et limiter strictement son périmètre. Une fonction pouvant fonctionner sous JWT utilisateur et RLS ne doit pas être élevée sans nécessité** | Cartographie §7 *(apport propre)* · ADR-005 §9 | Encadre l'unique voie de contournement de RLS | Catégories A et B d'Edge Functions | **A7** · **E10** · ADR-008 §7 · ADR-015 §7 |
| **A16** | **L'état métier, l'événement métier et l'attribution nécessaire à l'imputabilité doivent rester cohérents. Les logs techniques et de diagnostic peuvent être produits hors transaction et ne doivent pas bloquer l'opération métier** | Cartographie §7 · Cartographie §7 bis | Distingue ce qui doit être atomique de ce qui ne doit jamais bloquer | Étapes ⑪ et ⑫ | **E1** · **O2** · ADR-007 §5 et §16 · ADR-012 §2 |

---

## 3. Famille I — Identité et acteurs

> ### ⚠️ Anomalie documentaire majeure
>
> **Le document source de cette famille — la *Cartographie conceptuelle du modèle d'identité* — est référencé par le corpus mais absent du dépôt.**
>
> - **ADR-002 §12** : « Les invariants **I1 à I19** de la famille *Identité et acteurs* […] sont formulés dans le document transverse **"Cartographie conceptuelle du modèle d'identité"**, validé au même titre que le corpus. »
> - **ADR-003 §1** en fait une dépendance formelle : « Dépend des ADR-001 et 002 (acceptées) **et de la cartographie conceptuelle du modèle d'identité (validée)**. »
> - **ADR-004 §1 et §16** la citent également.
>
> `docs/architecture/cartographies/` ne contient que `AUTHORIZATION_FLOWS.md`.
>
> **Conséquence** : **onze** invariants de cette famille sont reconstituables par leurs citations dans le corpus disponible ; **huit** ne le sont pas. **Aucune formulation n'est inventée ici.**
>
> **Cette anomalie est signalée, non corrigée.** Voir §16, anomalie 1.

### 3.1 Invariants I reconstituables par citation

*Les intitulés ci-dessous sont **repris mot pour mot** des citations qu'en font les ADR disponibles. Ils ne constituent pas la formulation canonique, laquelle réside dans le document absent.*

| # | Intitulé, tel que cité par le corpus | ADR citante | Rôle | Domaine protégé | Références croisées |
|---|---|---|---|---|---|
| **I1** | « Ni agent IA ni service n'a de compte humain connectable » | ADR-003 §18, §11 · ADR-009 §6 | Prive structurellement les non-humains de capacité d'authentification | Nature de l'acteur | ADR-002 §5 *(motif de rejet de l'option A)* · **A14** |
| **I2** | *(cité sans formulation complète)* — « le rôle seul n'ouvre rien **(I2)** » | ADR-003 §6.4, §18 · ADR-004 §7, §16 | Impose l'affectation comme vecteur d'accès | Orthogonalité des axes d'autorisation | **A2**, **A3**, **A4** |
| **I4** | « Preuve de possession pour le rattachement » | ADR-003 §18 · ADR-002 §8.4 | Interdit un rattachement de contact sur un e-mail non vérifié | Rattachement contact ↔ acteur | ADR-003 §6.2 *(le lien magique le satisfait par construction)* |
| **I6** | « Acteur désactivé ⇒ refus global immédiat » | ADR-003 §18 · ADR-004 §10 · ADR-009 §8 · ADR-011 §3 · ADR-015 §5 | Rend la désactivation immédiate et globale, sans réviser chaque policy | Révocation | **A9** · ADR-002 §8.7 · Refus 2 |
| **I10** | « Un acteur n'est jamais supprimé » | ADR-003 §7, §18 · ADR-010 §11 · ADR-011 §3 · ADR-012 §3.1 | Préserve l'intelligibilité de l'audit après effacement RGPD | Fin de vie de l'acteur | ADR-002 §8.5 *(effacement à trois couches)* · **SEC1** |
| **I12** | « Acteur IA résoluble vers une configuration versionnée » | ADR-003 §11, §18 · ADR-009 §6 | Rend une action IA auditée interprétable | Auditabilité de l'IA | **IA2** · **IA5** · ADR-002 §6 bis |
| **I14** | « Acteur de service dédié à un usage » | ADR-003 §9, §18 · ADR-004 §10 · ADR-008 §9 | Empêche un acteur générique de masquer l'auteur réel | Attribution des traitements automatiques | **A13** · **I17** |
| **I15** | « Révoquer l'acteur et révoquer le moyen d'accès sont deux opérations distinctes » | ADR-003 §6.3, §6.4, §18 · ADR-009 §16 · ADR-011 §3, §9 | Impose les deux opérations, aucune ne suffisant seule | Révocation complète | **A9** · **I6** · ADR-003 §8.1 |
| **I16** | « Une dégradation d'attribution n'élève jamais les privilèges » | ADR-004 §12, §16 · ADR-011 §2, §13 · ADR-012 §11 | Interdit tout repli d'attribution sur une action sensible | Actions sensibles | **A5** · **⛔3** · Flux B |
| **I17** | « Action système attribuée à un acteur de service » | ADR-008 §9, §13 | Interdit l'attribution `NULL` d'une action système | Attribution des traitements planifiés | **A13** · **I14** |
| **I19** | « Unicité acteur par personne » — **invariant applicatif**, aucune contrainte de base ne peut le garantir | ADR-010 §9, §20 | Empêche que deux acteurs désignent la même personne physique | Lien contact ↔ acteur | ADR-002 §8.4 *(fusion de doublons)* |

### 3.2 Invariants I déclarés mais jamais cités

| # | État | Conséquence |
|---|---|---|
| **I3** · **I5** · **I7** · **I8** · **I9** · **I11** · **I13** · **I18** | **Ni formulés, ni cités dans aucun document disponible du corpus** | Leur contenu est **inconnu** au regard du dépôt. Aucune vérification de cohérence ni de collision n'est possible à leur sujet |

**Huit invariants sur dix-neuf sont donc inaccessibles.** Voir §16, anomalie 7.

---

## 4. Famille ⛔ — Interdictions certaines

- **Source** : Cartographie des flux d'autorisation §4
- **Statut** : **acquises, non renégociables** — le corpus les qualifie explicitement ainsi

| # | Intitulé | ADR source | Rôle | Domaine protégé | Références croisées |
|---|---|---|---|---|---|
| **⛔1** | **Aucune donnée commerciale pour chauffeur et partenaire** | Cartographie §4 · ADR-004 §4 | Argument décisif de la séparation `missions` / `enquiries` | Cloisonnement commercial | **D1**, **D2** · ADR-005 §3 · ADR-007 §4 et §14 · ADR-010 §5 · ADR-011 §10 · ADR-015 §5 |
| **⛔2** | **Aucun accès direct de l'agent IA à sa configuration** | Cartographie §4 · ADR-009 §6 | Empêche l'auto-élargissement du périmètre d'un agent | Configuration d'agent | **A14** · **IA2** · ADR-005 §12 · ADR-009 §12 |
| **⛔3** | **Aucune action sensible sans acteur humain résolu** | Cartographie §4 · ADR-004 §12 | Ferme le repli d'attribution | Imputabilité | **A5** · **I16** · Flux B |
| **⛔4** | **Aucune opération externe sans affectation lorsqu'elle est requise** | Cartographie §4 · ADR-004 §3.1 | Impose l'affectation comme condition d'accès opérationnel | Accès chauffeur et partenaire | **A4** · Flux D, E |
| **⛔5** | Aucun rôle global produit par une organisation partenaire | Cartographie §4 · ADR-003 §6.4 · ADR-004 §6 | Empêche l'élévation d'un partenaire vers l'administration Horizon | Portée des rôles d'organisation | **A2** · ADR-004 §6 |
| **⛔6** | Aucune écriture humaine directe dans l'audit | Cartographie §4 | Préserve la valeur probante de l'audit | Intégrité de l'audit | **A16** · **O3** · ADR-006 §7.1 *(zone interdite 5)* |

---

## 5. Famille G — Gouvernance de l'extensibilité

- **Source** : ADR-006 §16 — **famille créée par l'ADR-006**

| # | Intitulé | ADR source | Rôle | Domaine protégé | Références croisées |
|---|---|---|---|---|---|
| **G1** | Toute interface porte un statut : Experimental · Stable · Deprecated · Removed | ADR-006 §6.1 | Rend explicite le niveau de garantie d'un contrat de domaine | Cycle de vie des contrats de domaine | **G2**, **G3** · ADR-005 §13 |
| **G2** | Passage à Stable : deux implémentations réelles, documentation, suite de tests de contrat, signature figée depuis un cycle | ADR-006 §6.1 | Empêche qu'un contrat devienne stable par usage plutôt que par décision | Qualité des contrats | **G1** · ADR-015 §12 *(contrôle documentaire)* |
| **G3** | Aucun retrait sans passage préalable par Deprecated | ADR-006 §6.1, §13 | Garantit une période de migration | Compatibilité | **G1** · **Q-T** *(ouverte)* |
| **G4** | Tout hook porte un niveau explicite ; **niveau 1 par défaut** ; la promotion est une décision | ADR-006 §7.2 | Empêche qu'un hook devienne contractuel par accident | Classification des hooks | **G5** · ADR-015 §12 |
| **G5** | Les zones interdites s'appliquent aux trois niveaux de hook, sans exception | ADR-006 §7.1, §7.2 | Un hook interne n'a pas plus de droits qu'un hook contractuel sur les chemins de sécurité | Sécurité | **S5** · ADR-015 §13 étape 7 |
| **G6** | Toute façade JS déclare version, compatibilité minimale, dépréciation, garanties, comportement en méthode absente et en configuration incomplète | ADR-006 §9.1 | Transforme un contrat implicite en contrat écrit | Contrats JavaScript | **G1** · ADR-014 §8 |
| **G7** | Une extension observe, enrichit, propose ou transforme une représentation autorisée — **elle ne modifie jamais un agrégat critique hors des commandes du domaine** | ADR-006 §5, §11 (**S7**) | Empêche une extension d'échapper à l'autorisation, l'idempotence et l'audit | Intégrité métier | **S7** · **E4** · ADR-007 §15.1 |
| **G8** | Commande, requête, webhook et événement sont quatre contrats complémentaires aux responsabilités distinctes | ADR-006 §8 | Empêche qu'un contrat soit employé à la place d'un autre | Contrats d'intégration | **E3** · ADR-007 §3.2 |

---

## 6. Famille S — Sécurité des extensions

- **Source** : ADR-006 §11
- **Note** : ces règles ont provoqué la renumérotation **S1–S5 → SEC1–SEC5** dans l'ADR-011. **Les règles S1 à S7 de l'ADR-006 sont restées strictement inchangées.**

| # | Intitulé | ADR source | Rôle | Domaine protégé | Références croisées |
|---|---|---|---|---|---|
| **S1** | **Aucune extension ne reçoit de secret** | ADR-006 §11 | Confine les secrets hors de la surface d'extension | Secrets | **SEC4** · ADR-003 §9 |
| **S2** | **Aucune extension ne reçoit `service_role`** | ADR-006 §11 | Applique le principe directeur P4 aux extensions | `service_role` | **A7** · ADR-001 §4 |
| **S3** | La sortie d'une extension est une **entrée non fiable** | ADR-006 §7.1, §11 | Impose validation et normalisation de toute valeur de retour de filtre | Validation | **G5** · ADR-006 §14 |
| **S4** | **Une extension ne peut pas élever ses droits** : elle s'exécute après une autorisation déjà décidée | ADR-006 §11 | Place l'extension en aval de la décision d'autorisation | Autorisation | **A15** · **G7** |
| **S5** | **Aucun hook ne permet de neutraliser RLS, l'autorisation, l'audit ou la validation serveur** | ADR-006 §7.1, §11 | Transforme les dix zones interdites en règle de sécurité | Chemins de sécurité | **G5** · ADR-015 §13 étape 7 |
| **S6** | Une extension ne peut pas modifier la qualification de sensibilité d'une action | ADR-006 §11 | Réserve la qualification au runtime | Qualification de sensibilité | **A6** · ADR-004 §12 bis |
| **S7** | **Une extension observe, enrichit, propose ou transforme une représentation autorisée — elle ne modifie jamais un agrégat critique hors des commandes du domaine** | ADR-006 §5, §11 | Identique à **G7**, énoncé côté sécurité | Intégrité métier | **G7** · **E4** |

---

## 7. Famille E — Événementiel et exécution

- **Sources** : ADR-007 §17 *(E1 à E5)* · ADR-008 §13 *(E6 à E10)*
- **Réservation de numérotation** : **E1 à E10 sont réservés aux ADR-007 et ADR-008.** **E11 à E15 ne sont pas utilisés** — ils ont été renommés **CT1 à CT5** *(§12)*. **La famille E11 à E15 est définitivement abandonnée.**

### 7.1 E1 à E5 — Architecture événementielle *(ADR-007)*

| # | Intitulé | ADR source | Rôle | Domaine protégé | Références croisées |
|---|---|---|---|---|---|
| **E1** | Les déclencheurs produisent uniquement des événements mécaniquement dérivables des données validées. Toute décision métier appartient au domaine | ADR-007 §5.1 | Rend acceptable la délégation de l'atomicité à la base sans y déplacer d'arbitrage | Frontière base / domaine · émission transactionnelle | ADR-001 §8 · **E6** · **A16** · ADR-010 §7 et §13 |
| **E2** | Le journal est monotone : ni modification, ni remplacement, ni réordonnancement, ni suppression individuelle. Seuls l'ajout, l'archivage et la purge documentée sont admis | ADR-007 §4.1 | Empêche la réécriture de l'histoire | Intégrité du journal des faits | **O1**, **O3** · **SEC1** · **IA2** · ADR-010 §11 · ADR-011 §3 |
| **E3** | Un handler ne déclenche jamais directement un autre handler | ADR-007 §6.1 | Empêche la recréation d'un couplage direct hors des dispositifs de réessai et d'audit | Découplage inter-modules | **G8** · ADR-006 §8 · **E10** |
| **E4** | Une réconciliation ne modifie jamais l'état métier | ADR-007 §15.1 | Sépare strictement observation et action | Séparation observation / action | **G7** · **O2** · ADR-011 §5 et §12 · ADR-012 §11 et §12 |
| **E5** | Les événements IA empruntent le pipeline commun, sans mécanisme dédié | ADR-007 §14.1 | Un chemin dédié à l'IA serait un chemin moins éprouvé, donc plus faible | Uniformité et sûreté du traitement | **A6** · **IA5** · ADR-004 §12 bis · ADR-009 §5, §11, §17 |

### 7.2 E6 à E10 — Architecture d'exécution *(ADR-008)*

| # | Intitulé | ADR source | Rôle | Domaine protégé | Références croisées |
|---|---|---|---|---|---|
| **E6** | `pg_cron` n'est jamais un orchestrateur métier | ADR-008 §5.1 | Prolonge la frontière des déclencheurs à la planification | Frontière base / orchestration | **E1** · ADR-001 §8 · ADR-010 §13 |
| **E7** | Les workers sont sans état métier durable | ADR-008 §3.1 | Rend tout worker arrêtable, remplaçable, duplicable | Reprise, parallélisation, remplaçabilité | **E10** · ADR-008 §8 *(levier 3)* · ADR-013 §9 et §15 |
| **E8** | Un drapeau de fonctionnalité ne constitue jamais un mécanisme d'autorisation | ADR-008 §11.1 | Impose qu'un drapeau désactivé rende la fonctionnalité **absente**, jamais « présente mais interdite » | Séparation activation / autorisation | **A11**, **A12** · **DPL2** · ADR-004 §13 · ADR-009 §10 et §16 · ADR-014 §6 · ADR-015 §8 |
| **E9** | Le blocage des communications hors Production est vérifiable automatiquement | ADR-008 §12.1 | Protège des personnes réelles, sans dépendre de la vigilance humaine | Protection de personnes réelles | **D6** · **SEC4** · ADR-011 §3 *(Q-Y)* · ADR-013 §12 · ADR-014 §9 · ADR-015 §14 |
| **E10** | Les garanties fonctionnelles des handlers sont indépendantes du runtime qui les exécute | ADR-008 §4.3 | Rend la migration d'un handler purement opérationnelle | Migration sûre entre runtimes | **E3**, **E7** · **A15** · ADR-008 §4.2 |

---

## 8. Famille IA — Architecture IA

- **Source** : ADR-009 §20
- **Renumérotation acquise** : ces invariants portaient les identifiants **I16 à I20** dans le texte source. Ils entraient en collision avec **I16 à I19** de la famille *Identité*. **La famille Identité reste inchangée** ; la famille IA a été renommée. **Formulations strictement inchangées.**
- **Réservation** : la famille **IA** est close à **IA5**.

| # | Intitulé | ADR source | Rôle | Domaine protégé | Références croisées |
|---|---|---|---|---|---|
| **IA1** | Le fournisseur de modèle est interchangeable sans impact sur le domaine métier | ADR-009 §12.1 | Transforme Q-AA en question contractuelle, non architecturale | Indépendance du domaine vis-à-vis d'un tiers | **Q-AA** · **IA4** · ADR-001 *(passerelle obligatoire)* · ADR-011 §3 |
| **IA2** | Les consignes système sont versionnées, immuables et auditables | ADR-009 §6.1 | Rend l'audit interprétable et le retour arrière possible | Interprétabilité de l'audit · retour arrière | **E2** *(même propriété de monotonie)* · **A14** · **I12** · ADR-010 §4 |
| **IA3** | Un outil produit le même effet métier quel que soit le modèle qui l'appelle | ADR-009 §7.1 | Fait porter la sécurité par l'outil, jamais par le modèle | Détermination de la sécurité par l'outil | **G1–G8** · ADR-006 §13 · ADR-015 §9 *(tests exécutables sans modèle)* |
| **IA4** | Les limites Horizon sont indépendantes du modèle économique du fournisseur IA | ADR-009 §12.2 | Interdit toute unité de facturation fournisseur dans le domaine | Étanchéité économique du domaine | **IA1** |
| **IA5** | Une décision IA importante est explicable et reproductible conceptuellement, sans exiger une reproduction bit à bit de la sortie | ADR-009 §11.1 | Exige d'expliquer le raisonnement autorisé, non la formulation | Explicabilité | **IA2** *(la version de consigne est requise)* · **E5** · ADR-012 §9 |

---

## 9. Famille D — Données

- **Source** : ADR-010 §20
- **Réservation** : la famille **D** est close à **D6**.
- ⚠️ **Collision de symboles signalée** : les identifiants **D1 à D6** de l'ADR-015 §13 désignent les **défauts de l'audit technique global**, sans aucun rapport avec cette famille. **Collision signalée, non résolue** — voir §15.

| # | Intitulé | ADR source | Rôle | Domaine protégé | Références croisées |
|---|---|---|---|---|---|
| **D1** | Une zone de confidentialité décrit un régime de gouvernance, pas un silo absolu | ADR-010 §3.1 | Corrige une formulation initiale trop absolue qui aurait interdit le contact d'exécution | Justesse du modèle de confidentialité | **⛔1** · **D2** · ADR-004 §5 · ADR-011 §10 |
| **D2** | Toute duplication inter-zone est minimale, justifiée, explicitement modélisée et soumise à une rétention propre | ADR-010 §3.1 | Encadre la traversée d'une frontière de confidentialité | Maîtrise des traversées de frontière | **D1**, **D3** · **SEC1** · ADR-010 §12 · ADR-011 §3 et §10 · ADR-012 §5 et §6.1 |
| **D3** | Le contact d'exécution est un instantané métier figé, jamais resynchronisé automatiquement | ADR-010 §6.1 | Préserve ce qui a réellement été communiqué à l'exécutant | Valeur de preuve · purge indépendante | **D2** · **Q-AE** *(tranchée)* · ADR-011 §5 |
| **D4** | Un agrégat est une frontière de cohérence, pas une table | ADR-010 §4.1 | Empêche de lire l'inventaire des relations comme une correspondance avec les agrégats | Lecture correcte de l'inventaire | ADR-001 §8.4 · **O2** |
| **D5** | Toute référence publique est opaque et non séquentielle | ADR-010 §10.1 | Fait de l'opacité un invariant de sécurité, non une préférence de format | Non-inférence du volume, de la chronologie et des liens entre dossiers | ADR-005 §7a *(reçu énumérable)* · **Q-Q** *(ouverte)* · ADR-011 §7 |
| **D6** | Le registre de migrations permet de reconstituer l'historique exact de chaque environnement | ADR-010 §15.1 | Rend un déploiement reconstituable, par environnement | Auditabilité des déploiements | **E9** · ADR-004 §13 · ADR-013 §5 et §12 · ADR-014 §4 et §9 · ADR-015 §10 et §14 |

---

## 10. Famille T — Invariants transversaux du modèle

- **Source** : ADR-010 §7 — **exigence posée par l'ADR-001 §8.4**
- **Particularité** : chaque invariant transversal est assorti de son **lieu unique de garantie**. « Ils ne sont pas renumérotés et n'appartiennent à aucune autre famille du corpus. »

| # | Intitulé | ADR source | **Lieu unique de garantie** | Domaine protégé | Références croisées |
|---|---|---|---|---|---|
| **T1** | Aucune mission créée pour une demande annulée ou clôturée | ADR-010 §7 | **Base** | Cohérence entre cycle commercial et cycle opérationnel | ADR-001 §8.4 · **E1** · ADR-010 §13 |
| **T2** | Aucune clôture de demande incompatible avec une mission active | ADR-010 §7 | **Base** | Idem | ADR-001 §8.4 · **T1** |
| **T3** | Une annulation produit une conséquence opérationnelle | ADR-010 §7 | **Domaine + événement** | **Seul invariant transversal relevant du domaine** — c'est une orchestration, que **E1** exclut de la base | **E1** · ADR-010 §19 |
| **T4** | Cohérence des données partagées entre les deux cycles | ADR-010 §7 | **Base** *(clés étrangères)* **+ copie explicite** *(contact d'exécution)* | Deux mécanismes selon la nature du lien | **D3** · ADR-010 §6 |
| **T5** | Une affectation n'existe que pour une mission existante | ADR-010 §7 | **Base** | Intégrité référentielle | **T6** |
| **T6** | Une mission a exactement une demande d'origine | ADR-010 §7 | **Base** | Intégrité référentielle | **T5** · **Q-AF** *(ouverte)* |

> **Règle générale** *(ADR-010 §7)* : **un invariant qui engage deux agrégats est garanti en base ; un invariant qui déclenche une suite appartient au domaine.**

---

## 11. Famille SEC — Sécurité et conservation

- **Source** : ADR-011 §13
- **Renumérotation acquise** : ces invariants portaient les identifiants **S1 à S5** dans le texte source, en collision avec les règles **S1 à S7** de l'ADR-006. **Les règles S1 à S7 de l'ADR-006 restent strictement inchangées** ; la famille de sécurité a été renommée. **Formulations strictement inchangées.**
- **Réservation** : la famille **SEC** est close à **SEC5**.

| # | Intitulé | ADR source | Rôle | Domaine protégé | Références croisées |
|---|---|---|---|---|---|
| **SEC1** | Les catégories de rétention sont architecturales ; leurs durées sont des paramètres de politique | ADR-011 §3.1 | Permet d'ajuster une durée sans rouvrir l'architecture | Stabilité de l'architecture face aux arbitrages d'exploitation | **E2** · **D2** · **CT1** · **Q-AE**, **Q-W**, **Q-AD**, **Q-G**, **Q-AL** · ADR-012 §13 et §14 · ADR-013 §2.1 |
| **SEC2** | Une sauvegarde et une archive répondent à deux finalités différentes et ne sont jamais assimilées | ADR-011 §6.1 | Interdit de consulter une sauvegarde comme un historique | Non-contournement des règles d'accès et d'effacement | **D1**, **D2** · ADR-011 §6 *(trois conditions)* · ADR-013 §4 |
| **SEC3** | Un export est un objet temporaire gouverné, jamais une copie sans propriétaire | ADR-011 §5.1 | Empêche qu'une copie de la base subsiste hors de ses protections | Maîtrise des copies sorties de la base | **C3** *(critère de sensibilité)* · **SEC4** · ADR-012 §5 et §14 |
| **SEC4** | Tout secret possède un propriétaire, une procédure de rotation et une responsabilité explicite | ADR-011 §4.1 | Rend la rotation praticable — un secret sans propriétaire n'est jamais tourné | Faisabilité pratique de la rotation | ADR-003 §9 *(protocole en dix étapes)* · **E9** · **S1** · **CT3** · ADR-013 §7 |
| **SEC5** | Tout incident est qualifié avant traitement selon une classification commune | ADR-011 §9.1 | Détermine qui est alerté, dans quel délai, et si une notification externe est requise | Proportionnalité et rapidité de la réponse | **A5** · **O3**, **O5** · ADR-011 §9 · ADR-012 §8 et §15.1 · ADR-013 §11 |

---

## 12. Famille O — Observabilité

- **Source** : ADR-012 §18
- **Réservation** : la famille **O** est close à **O5**.

| # | Intitulé | ADR source | Rôle | Domaine protégé | Références croisées |
|---|---|---|---|---|---|
| **O1** | Aucun identifiant ne change de signification après sa création | ADR-012 §3.1 | Distingue les trois identifiants réutilisables à dessein des trois qui ne le sont jamais | Reconstitution fiable d'un parcours | **E2** *(`event_id` immuable)* · **DPL3** · ADR-005 §15 · **Q-P**, **Q-W** |
| **O2** | Les métriques ne remplacent jamais les données métier | ADR-012 §8.1 | Empêche de fonder une décision — ou une preuve — sur une approximation datée | Intégrité de la décision et de la preuve | **A16** · **E2** · **E4** · **D4** |
| **O3** | Le journal de sécurité est append-only | ADR-012 §2.1 | Un journal de sécurité modifiable ne prouve rien | Valeur probante en cas d'incident | **E2** *(même régime)* · **⛔6** · **SEC5** · **Q-AG** *(ouverte)* |
| **O4** | Les métriques sont statistiques et ne contiennent pas de données personnelles identifiantes | ADR-012 §6.1 | Empêche qu'une métrique devienne un second stockage de données personnelles hors zone | Non-création d'un stockage hors zone | **D2** · **SEC1** *(rétention propre)* |
| **O5** | Les alertes importantes sont elles-mêmes auditables | ADR-012 §7.1 | Rend applicable la règle « une alerte sans action est supprimée » | Effectivité de **R2** *(ADR-012 §7)* | **R1**, **R2** *(ADR-012 §7)* · **SEC5** |

---

## 13. Famille CT — Continuité

- **Source** : ADR-013 §16
- **Renumérotation acquise** : ces invariants portaient les identifiants **E11 à E15**. La famille **E** était surchargée. **E1 à E10 restent strictement inchangés** ; la famille de continuité a été renommée. **La famille E11 à E15 est définitivement abandonnée.** **Formulations strictement inchangées.**
- **Réservation** : la famille **CT** est close à **CT5**.

| # | Intitulé | ADR source | Rôle | Domaine protégé | Références croisées |
|---|---|---|---|---|---|
| **CT1** | Tout service critique possède des objectifs de continuité explicitement définis | ADR-013 §2.1 | Impose des RTO et RPO documentés, sans que l'architecture fixe de valeurs | Proportionnalité de la réponse à l'indisponibilité | **SEC1** *(les valeurs sont des paramètres)* · ADR-009 §3 *(l'IA est à objectifs illimités)* |
| **CT2** | Toute dépendance externe possède une stratégie de dégradation documentée | ADR-013 §6.1 | Impose qu'une dégradation **ferme, jamais n'ouvre** | Comportement maîtrisé en cas de défaillance d'un tiers | **CT4** *(vérifiée par exercice)* · ADR-009 §3 · **Q-A** · ADR-011 §7 *(CMP)* |
| **CT3** | Les procédures suivent le même cycle documentaire que les ADR | ADR-013 §11.1 | Distingue une capacité d'une hypothèse par la date de dernière exécution réussie | Distinction capacité / hypothèse | **CT4** · **SEC4** *(procédure de rotation)* · ADR-015 §11, §12, §14 |
| **CT4** | Les procédures critiques sont périodiquement éprouvées par simulation | ADR-013 §5.1 | Vérifie la réalité des dispositifs de protection | Réalité des dispositifs de protection | **CT2**, **CT3** · ADR-011 §6 *(effacements rejoués)* · **Q1**, **Q-AL** · ADR-015 §11 et §14 |
| **CT5** | Toute procédure identifie clairement les responsabilités opérationnelles | ADR-013 §11.2 | Supprime l'implicite en incident — le rôle de validation du retour à la normale est le plus souvent omis | Absence d'implicite en situation d'incident | **SEC5** *(qualification préalable)* · **DPL5** · ADR-013 §8 |

---

## 14. Famille DPL — Déploiement

- **Source** : ADR-014 §15
- **Réservation** : la famille **DPL** est close à **DPL5**.
- **Note** : aucune renumérotation n'a été nécessaire dans l'ADR-014.

| # | Intitulé | ADR source | Rôle | Domaine protégé | Références croisées |
|---|---|---|---|---|---|
| **DPL1** | Chaque mise en production possède un manifeste de livraison unique et versionné | ADR-014 §10.1 | Rend le périmètre déclaré avant réellement opposable | Opposabilité du périmètre déclaré | **CT3** *(artefact versionné)* · ADR-014 §9 *(dix critères)* · ADR-015 §14 |
| **DPL2** | Aucun drapeau temporaire ne devient permanent sans justification explicite | ADR-014 §6.1 | Oblige à choisir entre retirer un drapeau et le reclasser en option produit | Non-accumulation de dette d'activation | **E8** · ADR-014 §6 · ADR-015 §12 et §14 |
| **DPL3** | Tout changement de comportement peut être relié à un déploiement identifié | ADR-014 §3.1 | Répond à « qu'est-ce qui a changé juste avant ? » | Reconstitution de la cause en incident | **O1** · ADR-012 §3 · ADR-015 §14 |
| **DPL4** | Chaque environnement possède une gouvernance explicite | ADR-014 §11.1 | Impose une **cohérence documentée**, non une identité absolue — un écart ignoré est interdit | Détection des divergences entre environnements | ADR-013 §12 *(rien n'est partagé)* · **Q-AK** *(ouverte)* · ADR-015 §8 |
| **DPL5** | La validation post-déploiement fait partie intégrante du déploiement | ADR-014 §3.2 | Maintient le retour arrière disponible pendant toute l'observation | Disponibilité effective du retour arrière | **CT5** *(qui valide le retour à la normale)* · ADR-014 §5 · ADR-015 §14 |

---

## 15. Séries d'identifiants hors famille

*Ces séries emploient des symboles de type invariant sans constituer des familles d'invariants du corpus. Elles sont recensées ici pour signaler leurs collisions.*

### 15.1 Série C — deux emplois distincts

| Emploi | Identifiants | Source | Objet |
|---|---|---|---|
| **C-Edge** | C1, C2, C3, C4 | **ADR-001 §15** | **Critères déclenchant l'introduction des Edge Functions** |
| **C-Sensibilité** | C1, C2, C3, C4, C5, C6 | **ADR-011 §2** | **Critères de qualification d'une action sensible** |

**Collision confirmée sur C1 à C4.** Elle est désambiguïsée dans les faits par la mention de l'ADR d'origine à chaque citation *(par exemple ADR-012 §4 : « critère C3 de l'ADR-011 »)*, **mais elle n'est signalée nulle part dans le corpus.** Voir §16, anomalie 3.

### 15.2 Série R — deux emplois distincts

| Emploi | Identifiants | Source | Objet |
|---|---|---|---|
| **R-Approbation** | R1, R2, R3 | **ADR-009 §9** | **Règles d'approbation humaine d'une action IA** — R1 : on ne peut approuver que ce que l'on pourrait faire soi-même · R2 : l'approbation est elle-même une action sensible · R3 : une approbation expire |
| **R-Alerte** | R1, R2 | **ADR-012 §7** | **Règles d'alerte** — R1 : l'absence de signal doit alerter · R2 : une alerte sur laquelle personne n'agit doit être supprimée |

**Collision confirmée sur R1 et R2.** Les citations ultérieures sont ambiguës sans le contexte : ADR-013 §16 cite « **R1** *(l'absence de signal alerte)* » *(sens ADR-012)*, tandis qu'ADR-015 §17 cite « **R1**, **R3** *(approbation dans la limite de ses droits, approbation qui expire)* » *(sens ADR-009)*. **Cette collision n'est signalée nulle part dans le corpus.** Voir §16, anomalie 2.

### 15.3 Série D de l'audit technique — collision signalée par le corpus

| Emploi | Identifiants | Source | Objet |
|---|---|---|---|
| **D-Données** | D1 → D6 | **ADR-010 §20** | **Invariants de la famille Données** |
| **D-Défauts** | D1 → D6 | **ADR-015 §13** | **Défauts relevés par l'audit technique global** |

**Cette collision est la seule que le corpus signale explicitement.** ADR-015 §13 porte un avertissement de lecture :

> « Les identifiants **D1 à D6** de la colonne "Corrige" désignent les **défauts relevés par l'audit technique global**, et **non** les invariants **D1 à D6** de la famille *Données* de l'ADR-010. Les deux séries portent les mêmes symboles sans aucun rapport entre elles. **Cette collision de symboles est signalée, non résolue** : elle appelle une décision documentaire distincte. »

**Renommage futur envisagé : DEF1 à DEF6** — voir §17.

---

## 16. Contrôles de cohérence — résultats

*Les contrôles demandés ont été exécutés sur l'intégralité du corpus disponible. **Les résultats sont rapportés tels quels, y compris lorsqu'ils sont négatifs. Aucune anomalie n'a été corrigée.***

### 16.1 Contrôle des doublons — ✅ **AUCUN DOUBLON**

Aucun identifiant n'est attribué deux fois **au sein d'une même famille**. Chaque famille est continue et close à son dernier identifiant, conformément à ses réservations de numérotation.

| Famille | Continuité | Réservation déclarée |
|---|---|---|
| A | A1 → A16, continue | — |
| ⛔ | ⛔1 → ⛔6, continue | — |
| I | I1 → I19, continuité **invérifiable** *(source absente)* | — |
| G | G1 → G8, continue | — |
| S | S1 → S7, continue | Inchangée par la renumérotation SEC |
| E | E1 → E10, continue | **E1–E10 réservés** ; E11–E15 abandonnés |
| IA | IA1 → IA5, continue | **Close à IA5** |
| D | D1 → D6, continue | **Close à D6** |
| T | T1 → T6, continue | Hors de toute autre famille |
| SEC | SEC1 → SEC5, continue | **Close à SEC5** |
| O | O1 → O5, continue | **Close à O5** |
| CT | CT1 → CT5, continue | **Close à CT5** |
| DPL | DPL1 → DPL5, continue | **Close à DPL5** |

### 16.2 Contrôle des collisions — ⚠️ **QUATRE COLLISIONS**, dont trois résolues et une signalée

**Collisions résolues par renumérotation — acquises :**

| Collision | Résolution | ADR |
|---|---|---|
| **I16–I20** *(IA)* vs **I16–I19** *(Identité)* | **I16→IA1 · I17→IA2 · I18→IA3 · I19→IA4 · I20→IA5.** Famille Identité inchangée | ADR-009 §20 |
| **S1–S5** *(sécurité)* vs **S1–S7** *(extensions)* | **S1→SEC1 … S5→SEC5.** Règles S1–S7 de l'ADR-006 inchangées | ADR-011 §13 |
| **E11–E15** *(continuité)* vs surcharge de la famille E | **E11→CT1 … E15→CT5.** E1–E10 inchangés | ADR-013 §16 |

**Collision signalée par le corpus, non résolue :**

| Collision | Statut |
|---|---|
| **D1–D6** *(défauts de l'audit, ADR-015 §13)* vs **D1–D6** *(invariants Données, ADR-010 §20)* | **Signalée par un avertissement de lecture, explicitement non résolue.** Renommage futur **DEF1 à DEF6** — §17 |

**Collisions détectées par le présent recensement, non signalées par le corpus :**

| # | Collision | Portée |
|---|---|---|
| **1** | **R1, R2** — règles d'approbation IA *(ADR-009 §9)* vs règles d'alerte *(ADR-012 §7)* | Deux séries actives, citées dans quatre ADR |
| **2** | **C1 à C4** — critères d'introduction des Edge Functions *(ADR-001 §15)* vs critères de sensibilité *(ADR-011 §2)* | Deux séries actives, citées dans trois ADR |

> **Ces deux collisions sont signalées, non corrigées.** Elles n'empêchent la lecture d'aucun passage — chaque citation nomme son ADR d'origine — mais elles constituent une ambiguïté documentaire de même nature que celle que l'ADR-015 §13 a jugé nécessaire de signaler pour la série D.

### 16.3 Contrôle des références cassées — ⚠️ **UNE RÉFÉRENCE CASSÉE MAJEURE**

| Référence | Cible | État |
|---|---|---|
| ADR-002 §12 → *Cartographie conceptuelle du modèle d'identité* | `docs/architecture/cartographies/` | ❌ **ABSENTE** |
| ADR-003 §1 → *idem*, en dépendance formelle | `docs/architecture/cartographies/` | ❌ **ABSENTE** |
| ADR-003 §18 → *idem*, pour I1, I2, I4, I6, I10, I12, I14, I15 | `docs/architecture/cartographies/` | ❌ **ABSENTE** |
| ADR-004 §1 et §16 → *idem*, pour I2, I6, I14, I16 | `docs/architecture/cartographies/` | ❌ **ABSENTE** |
| ADR-004 §16 → `docs/architecture/cartographies/AUTHORIZATION_FLOWS.md` | Chemin explicite | ✅ **Présent et exact** |
| Toutes les références inter-ADR *(ADR-001 à ADR-015)* | `docs/architecture/adr/` | ✅ **Toutes résolues** — les quinze fichiers existent |

**Aucune ADR ne référence une ADR inexistante.** La seule référence cassée du corpus porte sur le document transverse d'identité.

### 16.4 Contrôle de cohérence des familles — ✅ **COHÉRENT**

- Chaque famille déclare son ADR source et sa réservation de numérotation.
- Chaque renumérotation est documentée dans l'historique de consolidation de l'ADR concernée, avec la mention « formulations strictement inchangées ».
- Aucun invariant ne change de formulation entre son ADR d'origine et ses citations ultérieures.
- La famille **T** est explicitement déclarée hors de toute autre famille *(ADR-010 §20)*.

**Une seule inexactitude relevée** : **ADR-001 §18** énonce la liste des familles du corpus comme `(I, A, E, CT, G, D, T, S, O, IA, DPL, R, C)`. Cette liste **omet SEC et ⛔**, et **inclut R et C**, qui ne sont pas des familles d'invariants mais des séries de règles et de critères *(§15)*. Voir §16.7, anomalie 5.

### 16.5 Contrôle des références croisées — ✅ **COHÉRENT**

Toutes les références croisées entre invariants ont été vérifiées. **Aucune ne pointe vers un identifiant inexistant.** Les références aux invariants **I** pointent vers des identifiants dont la formulation n'est pas disponible, mais dont l'existence est déclarée par l'ADR-002 §12.

**Trois références croisées particulièrement structurantes**, vérifiées :

| Référence | Vérification |
|---|---|
| **E1** ← **E6** — la frontière de `pg_cron` prolonge celle des déclencheurs | ✅ Cohérente : les deux excluent l'orchestration de la base |
| **E2** ← **IA2** ← **O3** — chaîne de monotonie | ✅ Cohérente : journal d'événements, consignes d'agent et journal de sécurité relèvent du même régime |
| **A5** ← **⛔3** ← **I16** — chaîne de l'action sensible | ✅ Cohérente : acteur humain requis, interdiction acquise, aucun repli d'attribution |

### 16.6 Contrôle de cohérence des questions ouvertes — ✅ **COHÉRENT**

- **19 questions ouvertes** à la clôture du corpus, listées par l'ADR-015 §22.
- **24 questions tranchées** au fil des ADR, chacune assortie de sa décision.
- Aucune question n'est déclarée tranchée dans une ADR puis rouverte dans une autre.
- Aucune question n'est référencée sans avoir été posée. **Une seule coquille avait été relevée et corrigée par le corpus lui-même** : « Q-AY » → « **Q-Y** » *(ADR-011 §7, historique de consolidation)*.

### 16.7 Anomalies documentaires restantes — recensement complet

> **Ces anomalies sont signalées et non corrigées, conformément au périmètre du présent document.**

| # | Anomalie | Localisation | Gravité | Signalée par le corpus ? |
|---|---|---|---|---|
| **1** | **Cartographie conceptuelle du modèle d'identité absente du dépôt** — les invariants **I1 à I19** ne sont formulés nulle part | ADR-002 §12 · ADR-003 §1, §18 · ADR-004 §1, §16 | **Majeure** | ❌ Non |
| **2** | **Collision R1/R2** — règles d'approbation IA vs règles d'alerte | ADR-009 §9 ↔ ADR-012 §7 ; citées en ADR-013 §16 et ADR-015 §17 | Moyenne | ❌ Non |
| **3** | **Collision C1–C4 / C1–C6** — critères Edge Functions vs critères de sensibilité | ADR-001 §15 ↔ ADR-011 §2 ; citées en ADR-008 §16 et ADR-012 §4 | Moyenne | ❌ Non |
| **4** | **Collision D1–D6** — défauts de l'audit vs invariants Données | ADR-015 §13 ↔ ADR-010 §20 | Moyenne | ✅ **Oui, explicitement non résolue** |
| **5** | **Liste des familles d'invariants incomplète et inexacte** — omet **SEC** et **⛔**, inclut **R** et **C** | ADR-001 §18 | Faible | ❌ Non |
| **6** | **Renvoi possiblement obsolète** — « ADR-013 » cité pour le CRM, sous une numérotation où ADR-013 portait l'observabilité. L'ADR-001 ne comporte **aucune note de réalignement**, contrairement aux ADR-002, 005, 008 et 011 | ADR-001 §16, ligne « CRM / Back-office » | Faible | ❌ Non |
| **7** | **Huit invariants I jamais cités** — **I3, I5, I7, I8, I9, I11, I13, I18** n'apparaissent nulle part | Corpus entier | Faible | ❌ Non — conséquence de l'anomalie 1 |
| **8** | **Dates de décision non consignées** — les quinze ADR portent « Date : non consignée dans la source » | En-tête des quinze ADR | Faible | ✅ Oui, par chaque ADR |
| **9** | **Numérotation de condition d'acceptation** — la condition 3 est libellée « *(2 bis)* », vestige d'une insertion | ADR-005 §25 | Cosmétique | ✅ Oui, historique de consolidation |
| **10** | **Bloc « Clôture du corpus architectural » non intégré** — écarté au motif qu'il constituait une référence globale d'architecture « dont la création n'est pas autorisée à ce stade » | ADR-015, note finale | Information | ✅ Oui — **le présent lot en est la réalisation autorisée** |

**Aucune de ces anomalies ne remet en cause une décision d'architecture.** Elles portent toutes sur la forme documentaire.

---

## 17. Corrections documentaires futures

*Section prévue par le périmètre du présent lot. **Aucune correction n'est appliquée ici.***

### 17.1 Renommage futur DEF1 à DEF6

**Objet** : les identifiants **D1 à D6** employés à l'**ADR-015 §13**, colonne « Corrige », désignent les **six défauts relevés par l'audit technique global**, sans aucun rapport avec les invariants **D1 à D6** de la famille *Données* de l'**ADR-010 §20**.

**Statut actuel** : la collision est **signalée par l'ADR-015 §13** au moyen d'un avertissement de lecture, et **explicitement non résolue**. L'ADR-015 précise : « elle appelle une **décision documentaire distincte** ».

**Renommage envisagé** :

| Identifiant actuel | Identifiant futur | Objet, tel que désigné par l'ADR-015 §13 |
|---|---|---|
| D1 *(ADR-015 §13, étape 1)* | **DEF1** | Défaut de reproductibilité — absence de verrou de dépendances |
| D2 *(ADR-015 §13, étape 1)* | **DEF2** | Défaut d'installation — l'installation tolère son propre échec |
| D3 *(ADR-015 §13, étape 2)* | **DEF3** | Défaut d'analyse statique |
| D4 *(ADR-015 §13, étape 2)* | **DEF4** | Défaut d'analyse statique *(second volet)* |
| D5 *(ADR-015 §13, étape 5)* | **DEF5** | Absence de tests de contrats d'API |
| D6 *(ADR-015 §13, étape 2)* | **DEF6** | Défaut d'analyse statique *(troisième volet)* |

> **Réserve de fidélité** : le corpus ne détaille nulle part le contenu individuel des six défauts **D1 à D6** de l'audit technique global — il les référence par leur seul identifiant, dans la colonne « Corrige » du tableau de l'ADR-015 §13. **Les objets indiqués ci-dessus sont déduits de la ligne du tableau où chaque identifiant apparaît, et non d'une formulation explicite du corpus.** Le rapport d'audit technique global n'est pas présent dans `docs/architecture/`.

**Ce renommage n'est pas appliqué.** Il relève d'une décision documentaire distincte, comme l'ADR-015 le prévoit.

### 17.2 Autres corrections à envisager

*Recensées, non appliquées.*

| # | Correction envisageable | Portée |
|---|---|---|
| **1** | **Verser au dépôt la *Cartographie conceptuelle du modèle d'identité***, ou, à défaut, formuler explicitement I1 à I19 dans un document du corpus | **Prioritaire** — c'est la seule référence cassée du corpus, et elle porte sur onze invariants activement cités |
| **2** | **Désambiguïser la série R** — par exemple `R-IA1..3` et `R-ALT1..2`, ou par un avertissement de lecture sur le modèle de celui de l'ADR-015 §13 | ADR-009 §9, ADR-012 §7 |
| **3** | **Désambiguïser la série C** — par exemple `C-EDGE1..4` et `C-SENS1..6` | ADR-001 §15, ADR-011 §2 |
| **4** | **Corriger la liste des familles de l'ADR-001 §18** — ajouter **SEC** et **⛔**, retirer **R** et **C** ou les qualifier de séries hors famille | ADR-001 §18 |
| **5** | **Vérifier le renvoi « ADR-013 » de l'ADR-001 §16** et, s'il relève de l'ancienne numérotation, ajouter une note de réalignement sur le modèle de celles des ADR-002, 005, 008 et 011 | ADR-001 §16 |
| **6** | **Consigner les dates de décision** des quinze ADR, aujourd'hui « non consignées dans la source » | En-tête des quinze ADR |
| **7** | **Corriger le libellé « *(2 bis)* »** de la condition 3 de l'ADR-005 §25 | ADR-005 §25 |

> **Aucune de ces corrections n'est appliquée par le présent lot.** Elles sont recensées pour une décision documentaire ultérieure.

---

## 18. Index alphabétique des invariants

| Identifiant | Famille | Section |
|---|---|---|
| A1 → A16 | Autorisation | §2 |
| CT1 → CT5 | Continuité | §13 |
| D1 → D6 | Données | §9 |
| DPL1 → DPL5 | Déploiement | §14 |
| E1 → E5 | Événementiel | §7.1 |
| E6 → E10 | Exécution | §7.2 |
| G1 → G8 | Gouvernance de l'extensibilité | §5 |
| I1 → I19 | Identité et acteurs — **source absente** | §3 |
| IA1 → IA5 | Architecture IA | §8 |
| O1 → O5 | Observabilité | §12 |
| S1 → S7 | Sécurité des extensions | §6 |
| SEC1 → SEC5 | Sécurité et conservation | §11 |
| T1 → T6 | Invariants transversaux | §10 |
| ⛔1 → ⛔6 | Interdictions certaines | §4 |
| **C1 → C4 / C1 → C6** | *Séries hors famille — critères* | §15.1 |
| **R1 → R3 / R1 → R2** | *Séries hors famille — règles* | §15.2 |
| **DEF1 → DEF6** | *Renommage futur, non appliqué* | §17.1 |

---

## Fin du document

> **Rappel** : ce document recense. **En cas de divergence, l'ADR source fait foi.** Aucun invariant n'y est créé, renuméroté ou modifié.
