
* Le projet a pour but de rendre plus pertinentes les recherches d'informations des CV sur le site web Beauté Job.

* L'objectif est de créer un programme permettant de récupérer les informations d'une personne ayant déposé son CV pour l'enregistrer et d'avoir un système plus efficace et pertinant pour la mise en relation des chercheurs d'emploi et des recruteurs.

* Le langage PHP est choisi pour des raisons internes (meilleure expertise), mais aussi car le site Beauté Job est principalement codé en PHP, donc cela permettra de mieux intégrer le programme de recherche.

> L'utilisation de l'**Intelligence Artificielle** est privilégiée car elle est plus fiable et flexible que l'utilisation de l'OCR de **Tesseract** et **Parser** pour extraire les informations.

> L'utilisation de **Parser** a pour but d'aider l'**IA** à trouver des informations qu'elle ne parvient pas à trouver.

Tableau des technologies utilisées :
===

|  Technologies  | Intérêts | Limites |
| :--------------- |:---------------:| -----:|
Parser et Tesseract |   Récupérer le texte à partir de fichier PDF ou d'un fichier image       |  Le texte peut être inexact.<br> La réponse n'est pas structurée.<br> Les fichiers sans texte ne sont pas traités|
| Mistral AI (API)  | Extraire des informations du CV et les mettre en forme. | Peut interpréter ou inventer.<br> Le temps de réponse est plus ou moins long (en moyenne 41 secondes) selon le nombre d'informations à traiter.<br> Les images peuvent ne pas être correctement traitées.<br> Ne parvient pas à détecter les icônes dans le CV. |

> Pour les icônes, l'IA ne parvient pas à les trouver. La solution serais d'utiliser des bibliothèques comme : **OpenCV, TensorFlow (Hub), PyTorch/TorchVision, Keras ou YOLO**. Cependant, ce moyen nécéssite l'utilisation de **Python**.

# Installation de PHP sur Windows
Pour utiliser et installer les extensions nécessaires au bon fonctionnement du programme il faut utiliser Composer et surtout une version de PHP égale ou supérieure à la version 8.2.
## Étape 1 : Installation PHP
1. Il faut donc vous rendre sur le site [PHP](https://www.php.net/downloads.php) et installez la version qui vous convient.
2. Une fois installé, rendez-vous sur le disque C: de l'explorateur de fichiers et créez un fichier PHP suivi du nom de la version (Exemple: php-8.3.21).
## Étape 2 : Ajouter PHP au PATH de Windows
1. Récupérer le chemin du fichier que vous avez créé précédemment (Exemple : C:\php-8.3.21)
2. Lancez le panneau de configuration, allez dans `Système et sécurité > Système > Paramètres avancés du système > Variables d'environnement` et dans `Variable système` cherchez le PATH et sélectionnez-le.
3. Une fois cliqué, sélectionner `Nouveau` puis ajouter le chemin de la nouvelle version de PHP.
4. Fermez les fenêtres.
5. Tester dans un terminal de commande si la nouvelle version de PHP a bien été enregistrée.
```bash
php -version
```

# Installation de PHP sur Linux (non testé)
Pour commencer, vérifiez les versions disponibles de PHP :
```bash
sudo apt-cache policy php
```
## Étape 1 : Installer la version de PHP qui vous souhaitez
1. Installer la version PHP (Exemple : php 8.3.21)
```bash
sudo apt-get install phpX.Y.Z
```
>**Note**
Remplacer X, Y et Z par le numéro de la version de PHP (Exemple pour PHP 8.4.21 : `sudo apt-get install php8.3.21`
)

## Étape 2 : Désactiver la version actuelle de PHP
1. Utiliser cette commande pour désactiver la version actuelle de PHP :
```bash
sudo a2dismod phpX.Y
```
>**Note**
Remplacer X, Y et Z par le numéro de la version de PHP (Exemple pour PHP 8.4.21 : `a2dismod php8.3.21`
)
## Étape 3 : Activer la nouvelle version de PHP
1. Activer le module de la nouvelle version de PHP :
>**Note**
Remplacer X, Y et Z par le numéro de la version de PHP (Exemple pour PHP 8.4.21 : `sudo apt-get install php8.3.21`
)
## Étape 4 : Redémarrer le serveur Apache
1. Pour appliquer les changements, redémarrez Apache avec cette commande :
```bash
sudo systemctl restart apache2
```
**Une fois les changements effectués vous pouvez vérifier la nouvelle version de PHP avec cette commande: `php -v`**
# Installation de Tesseract OCR

## Installation sur Windows

### Étape 1 : Télécharger l'installateur
1. Rendez-vous sur le dépôt GitHub de [UB Mannheim](https://github.com/UB-Mannheim/tesseract/wiki).
2. Téléchargez l'installateur pour Windows.

### Étape 2 : Exécuter l'installateur
1. Double-cliquez sur le fichier téléchargé pour lancer l'installation.
2. Suivez les instructions de l'installateur.
3. Assurez-vous de cocher l'option pour ajouter Tesseract à votre variable d'environnement PATH.

### Étape 3 : Vérifier l'installation
1. Ouvrez une nouvelle fenêtre d'invite de commandes (CMD).
2. Tapez la commande suivante pour vérifier l'installation :
```bash
   tesseract --version
```

## Installation pour Linux (non testé)

### Étape 1 : Mettre à jour les paquets
1. Ouvrez un terminal.
2. Mise à jour la liste des paquets:
```bash
sudo apt-get update
```

### Étape 2 : Installer Tesseract
1. Installez Tesseract avec cette commande:
```Bash
sudo apt-get install tesseract-ocr
```

2. (Facultatif) Installez les fichiers de langue supplémentaires si nécessaire. Par exemple, pour installer le support de la langue française :
```bash
sudo apt-get install tesseract-ocr-fra
```

### Étape 3 : Vérifier l'installation

1. Relancez le CMD et tapez cette commande:
```bash
tesseract --version
```

>**Attention** Vous pouvez recontrer cette erreur:
```
tesseract : Le terme «tesseract» n'est pas reconnu comme nom d'applet de commande, fonction, fichier de script ou
programme exécutable. Vérifiez l'orthographe du nom, ou si un chemin d'accès existe, vérifiez que le chemin d'accès
est correct et réessayez.
Au caractère Ligne:1 : 1
+ tesseract --version
+ ~~~~~~~~~
    + CategoryInfo          : ObjectNotFound: (tesseract:String) [], CommandNotFoundException
    + FullyQualifiedErrorId : CommandNotFoundException
```

Pour cela, il va falloir ajouté le chemin de Tesseract au Path:

## Ajouter Tesseract au PATH sur Windows

### Étape 1 : Trouver le chemin d'installation de Tesseract
1. Par défaut, Tesseract est généralement installé dans `C:\Program Files\Tesseract-OCR`.
2. Vérifiez que le dossier contient le fichier exécutable `tesseract.exe`.

### Étape 2 : Ajouter Tesseract au PATH
1. Ouvrez le Panneau de configuration :
2. Cliquez sur le menu Démarrer et tapez "Panneau de configuration", puis appuyez sur Entrée.
3. Allez dans Système et sécurité > Système.
4. Cliquez sur "Paramètres système avancés" sur le côté gauche.
5. Dans la fenêtre qui s'ouvre, cliquez sur le bouton "Variables d'environnement".
6. Sous la section "Variables système", trouvez la variable `Path`, sélectionnez-la et cliquez sur "Modifier".
7. Cliquez sur "Nouveau" et ajoutez le chemin d'installation de Tesseract (par exemple, `C:\Program Files\Tesseract-OCR` ou dans `C:\Users\Utilisateur\AppData\Local\Programs\Tesseract-OCR`).
8. Cliquez sur OK pour fermer toutes les fenêtres.

### Étape 3 : Vérifier l'ajout au PATH
1. Ouvrez une nouvelle fenêtre d'invite de commandes (CMD).
2. Tapez la commande suivante pour vérifier que Tesseract est accessible :
```bash
   tesseract --version
```

## Ajouter Tesseract au PATH sur Linux (non testé)

## Étape 1 : Vérifier l'installation de Tesseract
1. Ouvrez un terminal.
2. Tapez la commande suivante pour vérifier si Tesseract est installé :
```bash
   tesseract --version
```

3. Si Tesseract n'est pas installé, utilisez cette commande:
```bash
sudo apt-get install tesseract-ocr
```

## Étape 2 : Trouver le chemin d'installation de Tesseract

1. Par défaut, Tesseract est généralement installé dans /usr/bin.
2. Vérifiez que le binaire tesseract est présent dans ce dossier en utilisant la commande suivante :
```bash
which tesseract
```
3. Cette commande devrait retourner le chemin d'installation de Tesseract, par exemple /usr/bin/tesseract.

## Étape 3 : Ajouter Tesseract au PATH
1. Ouvrez votre fichier de configuration de shell dans un éditeur de texte. Par exemple, pour le shell Bash, utilisez la commande suivante :

```bash
nano ~/.bashrc
```
2. Ajoutez la ligne suivante à la fin du fichier pour ajouter le chemin d'installation de Tesseract à votre PATH :
```bash
export PATH=\$PATH:/usr/bin
```
3. Sauvegardez le fichier et fermez l'éditeur de texte.

## Étape 4 : Charger le fichier de configuration

1. Chargez le fichier de configuration de shell pour appliquer les modifications en utilisant la commande suivante :

```bash
source ~/.bashrc
```

## Étape 5 : Vérifier l'ajout au PATH

1. Tapez la commande suivante pour vérifier que Tesseract est accessible depuis n'importe quel répertoire :

```bash
tesseract --version
```

# Installation de l'API Mistral

## Installation pour Windows

1. Installez Composer si ce n'est pas déjà fait avec [la documentation Composer]('https://getcomposer.org/').

L'API de Mistral provient de Github : [lien vers l'API Mistral](https://github.com/HelgeSverre/mistral)

## Étape 1 : Installer l'API

1. Créer un projet PHP
2. Lancez l'invite de commande.
3. Placez-vous dans votre fichier créé, puis tapez la commande suivante : `composer require helgesverre/mistral`

## Étape 2 : Configurer les variables d'environnement

1. Tout d'abord, ouvrez le fichier nommé : `.env`. Il se trouve généralement à la racine de votre projet.

>**Note**: Ce fichier peut ne pas exister, créez-le en ouvrant un éditeur de texte (Notepad, VsCode ou autre) pour enregistrer dans le fichier racine de votre projet (exemple: c:/fichier_source_votre_projet/.env).

2. Entrer dans le fichier crée ce code :
```bash
MISTRAL_API_KEY=votre_clé_api
MISTRAL_BASE_URL=https://api.mistral.ai
MISTRAL_TIMEOUT=30
```

## Installation pour Linux (non testé)

## Étape 1 : Installer PHP (facultatif si PHP est déjà installer)
1. Tapez cette ligne : `sudo apt update`
2. Installer php avec cette commande : `sudo apt install php`

## Étape 2 : Installer Composer
1. Voici les commandes à tapé :
```bash
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
php -r "unlink('composer-setup.php');"
sudo mv composer.phar /usr/local/bin/composer
```

## Étape 3 : Créer un projet PHP
1. Placez-vous dans le fichier de votre projet dans l'invite de commande avec ces lignes : `mkdir mon-projet` puis `cd mon-projet`

## Étape 4 : Ajouter le Package Mistral
1. Dans le répertoire de votre projet écrivez cette commande :
```bash
composer require helgesverre/mistral
```
## Étape 5 : Configurer l'API
1. Écrivez cette ligne de commande pour ajouter le package installé précédement :
```bash
php artisan vendor:publish --tag="mistral-config"
```

## Étape 6 : Configurer les Variables d'Environnement
1. Ouvrez votre fichier `.env`
>**Note**: Ce fichier peut ne pas exister, créez-le en ouvrant un éditeur de texte (Notepad, VsCode ou autre) pour enregistrer dans le fichier racine de votre projet (exemple: c:/fichier_source_votre_projet/.env).
2. Copiez cette ligne de commande dans le fichier `.env` :
```bash
MISTRAL_API_KEY=votre_clé_api
MISTRAL_BASE_URL=https://api.mistral.ai
MISTRAL_TIMEOUT=30
```

# Installation de Parser
## Installation pour windows
Vérifiez bien si `PHP.exe` et `composer.exe` sont dans votre fichier **PATH**. Sinon, veuillez vous rendre plus haut dans la documentation pour les installer et les configurer.
## Étape 1 : Création du projet
1. Dans l'invite de commande, placez-vous dans le fichier de votre projet :
```bash
cd C:\Users\VotreNom\mon-projet
mkdir mon-projet
cd mon-projet
```
## Étape 2 : Installation de Smalot PDFparser
1. Une fois dans votre projet, veuillez exécuter cette commande :
```bash
composer require smalot/pdfparser
```

## Installation pour linux (non testé)
On se placera dans le **terminal avec tous les droits (sudo)** pour l'installation de l'extension. Veillez vérifier si **php** est bien installé avec ses extensions, sinon exécuter ces commandes :
```bash
sudo apt update
sudo apt install php php-cli php-xml php-mbstring unzip curl
```
Vérifiez aussi si **Composer** est installé, si ce n'est pas le cas, remontez un peu dans la documentation pour l'installer.
## Étape 1 : Création d'un projet.
1. Créez votre fichier de projet avec cette commande :
```bash
mkdir ~/chemin/vers/votre/projet
```
2. Placez-vous dans votre projet :
```bash
cd ~/chemin/vers/votre/projet
```
3. Initialisez Composer (facultatif) :
```bash
composer init
```
## Étape 2 : Installation de Smalot PDFparser
1. Installez Parser avec cette commande :
```bash
composer require smalot/pdfparser
```
## Étape 3 : Vérification de l'installation
Dans un fichier test en .php, entrez ce code :
```php
<?php
require __DIR__ . '/vendor/autoload.php';

use Smalot\PdfParser\Parser;

try {
    $parser = new Parser();
    echo "Parser instancié avec succès ! Version : " . Parser::VERSION . PHP_EOL;
} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage() . PHP_EOL;
}
```
Puis dans l'invite de commande, exécutez le code :
```bash
php votreFichierTest.php
```
Vous devriez avoir ce retour :
```bash
Parser instancié avec succès ! Version : votre_version_installée
```