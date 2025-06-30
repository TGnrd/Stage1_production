# Contexte :

**Altervoice**, une entreprise de 3 salariés créée en 2010, offre à ses clients un service de traitement des médias audio et vidéo. Il est crucial pour les entreprises qui veulent proposer des services numériques innovants.

**Thema-web** est une entreprise d'édition de site web intégré à l'entreprise Altervoice en 2022.

Cette entreprise comporte notament le site **Beauté Job**, un site Job board mettant en relation des chercheurs d'emploi et des recruteurs dans le secteur de la beauté (esthéticien(ne), maquilleur(se),coiffeur(se), etc...).

* C'est sur ce site web que mon projet va être intégrer.

# Le but de ce projet :

* Le projet a pour but de rendre plus pertinentes les recherches d'informations des CV sur le site web Beauté Job.

* L'objectif du programme est de récupérer les informations d'une personne ayant déposé son CV pour l'enregistrer et d'avoir un système plus efficace et pertinant pour la mise en relation des chercheurs d'emploi et des recruteurs.

# Technologies utilisées :

* Le langage PHP est choisi pour des raisons internes (meilleure expertise), mais aussi car le site Beauté Job est principalement codé en PHP, donc cela permettra de mieux intégrer le programme de recherche.

> L'utilisation de l'**Intelligence Artificielle** est privilégiée car elle est plus fiable et flexible que l'utilisation de l'OCR de **Tesseract** et **Parser** pour extraire les informations.

> L'utilisation de **Parser** a pour but d'aider l'**IA** à trouver des informations qu'elle ne parvient pas à trouver.

Tableau des technologies utilisées :
===

|  Technologies  | Intérêts | Limites |
| :--------------- |:---------------:| -----:|
Parser et Tesseract |   Récupérer le texte à partir de fichier PDF ou d'un fichier image       |  Le texte peut être inexact.<br> La réponse n'est pas structurée.<br> Les fichiers sans texte ne sont pas traités|
| Mistral AI (API)  | Extraire des informations du CV et les mettre en forme. | Peut interpréter ou inventer.<br> Le temps de réponse est plus ou moins long (en moyenne 25 secondes) selon le nombre d'informations à traiter.<br> Les images peuvent ne pas être correctement traitées.<br> Ne parvient pas à détecter les icônes dans le CV. |

> Pour les icônes, l'IA ne parvient pas à les trouver. La solution serais d'utiliser des bibliothèques comme : **OpenCV, TensorFlow (Hub), PyTorch/TorchVision, Keras ou YOLO**. Cependant, ce moyen nécéssite l'utilisation de **Python**.

# Fonctionnement :

* Lancement dans la console :

```bash
php phpUpload.php https://URL_CV.pdf
```

1. Récupération et vérification de l'URL, si elle comporte bien `http(s)` ou `.pdf`.

2. Lecture du CV et extraction du texte avec de l'OCR.

3. Enregistrement de la date et l'heure dans une variable.

4. Envoie de l'URL, de l'extraction OCR, des instructions et du format de réponse attendus à l'API de Mistral pour extraire plus d'informations et les organiser dans le format souhaité.

5. Récupération des informations traitées et enregistrement dans un fichier json.

# Le résultat des tests :

Entre **1500 et 2000 tests** du code sur **une vingtaine de CV différents** ont été effectuer lors de la production du code.

* Sur un écantillons de **110 tests**, le code se trouve avec un **taux de similarité de 88.05%** pour un temps d'exécution de **22.255 secondes**.

# Ce que j'ai appris lors de la création du projet :

* L'utilisation de l'**API de Mistral** (et en parallèle l'**API d'Open AI**).

* Approfondissement de mes connaissances de **PHP**.

* Première utilisation de **cURL** pour l'appel d'une API.

* Utilisation de **Postman** pour automatiser les tests (premier travail sur ce logiciel).

* Meilleure **utilisation** et **compréhension** de l'IA.

# Ce qu'il me reste à travailler :

* Apprendre à mieux expliquer quelque chose de technique sans aller dans les détails techniques (meilleure élocution).

* Avoir une meilleure compréhension du code.

* Avoir une meilleure compréhension sur les directives données.

# Sur le plan scolaire :

### B1.2 : Répondre aux incidents et aux demandes d’assistance et d’évolution

- B1.2.2 : Traiter des demandes concernant les applications

### B1.3 : Développer la présence en ligne de l’organisation

- B1.3.1 : Participer à l’évolution d’un site Web exploitant les données de l’organisation.

- B1.3.3 : Participer à la valorisation de l’image de l’organisation sur les médias numériques en tenant compte du cadre juridique et des enjeux économiques

### B1.4 : Travailler en mode projet

- B1.4.1 : Analyser les objectifs et les modalités d’organisation d’un projet

- B1.4.2 : Évaluer les indicateurs de suivi d’un projet et analyser les écarts

### B1.5 : Mettre à disposition des utilisateurs un service informatique (orienté utilisateurs)

- B1.5.1 : Déployer un service

- B1.5.2 : Réaliser les tests d’intégration et d’acceptation d’un service

### B1.6 : Organiser son développement professionnel

- B1.6.1 : Gérer son identité professionnelle

- B1.6.2 : Développer son projet professionnel

- B1.6.4 : Mettre en place son environnement d’apprentissage personnel

### B2.1 : Concevoir et développer une solution applicative

- B2.1.2 : Modéliser une solution applicative

- B2.1.3 : Participer à la conception de l’architecture d’une solution applicative

- B2.1.9 : Réaliser des tests nécessaires à la validation ou à la mise en production d’éléments adaptés ou développés

- B2.1.11 : Évaluer la qualité d’une solution applicative

- B2.1.12 : Rédiger des documentations techniques et d’utilisation d’une solution applicative

### B2.2 : Assurer la maintenance corrective ou évolutive d’une solution applicative

- B2.2.1 : Évaluer la qualité d’une solution applicative

- B2.2.3 : Analyser et corriger un dysfonctionnement

- B2.2.4 : Elaborer et réaliser des tests des éléments mis à jour

- B2.2.5 : Mettre à jour la documentation technique et d’utilisation d’une solution applicative

### B3.1 : Protéger les données à caractère personnel

- B3.1.2 : Identifier les risques liés à la collecte, au traitement, au stockage et à la diffusion des données à caractère personnel

- B3.1.3 : Appliquer la réglementation en matière de collecte, de traitement et de conservation des données à caractère personnel

### B3.2 : Préserver l'identité numérique de l’organisation

- B3.2.1 : Protéger l’identité numérique d’une organisation

### B3.4 : Garantir la disponibilité, l’intégrité et la confidentialité des services informatiques et des données de l’organisation face aux cyberattaques

- B3.4.1 : Caractériser les risques liés à l’utilisation malveillante d’un service informatique

- B3.4.2 : Recenser les conséquences d’une perte de disponibilité, d’intégrité ou de confidentialité.
