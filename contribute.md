# Contribution au projet Todo & Co

1.  **Création** d'un compte github *( https://github.com )*

2.  Maintenant que vous avez un compte sur Github, **allez sur le projet**
    *( https://github.com/Mickael861/todoandco.git )*.

3.  **cliquez** sur le bouton **“Fork”** situé en haut à droite de la page projet.

![GitHub bouton "fork"](/public/img/btn-fork.png)

4.  **Choisissez votre profil** afin que Github puisse **créer** un dépôt clone du projet sur votre profil Github.

## Démarrage

Maintenant que vous avez **fork** le projet Github, nous allons commencer à **cloner** le projet Symfony.

1.  Ouvrez **git Bash** et tapez la commande suivante : 
**HTTPS** :
1.1 git clone https://github.com/Mickael861/todoandco.git
**SSH** :
1.1 git clone git@github.com:Mickael861/todoandco.git

Maintenant que vous avez **cloner le projet** Todo & co dans votre dépôt, nous allons désormais commencer à **créer une branche** spécifique pour cette contribution.

1.  git checkout -b addTestDefaultController

Admettons que vous voulez **ajouter un test fonctionnel** au DefaultController
*(tests\Controller\WebTestCase\default\DefaultControllerTest.php)*

Vous allez faire un ajout dans ce fichier. Dès que vous avez fini votre ajout, nous allons faire les **pré requis** avant de faire un **commit** sur votre branche.

## Soumission de la contribution

Vous avez effectués les tests sur votre environnement local, on va maintenant **pousser** ce code sur votre dépôt **Git distant**.

1.  Ajouter les fichiers : *(git add ...Listes des fichiers)*
2.  Commit : *(git commit -m "Adding more tests")*
3.  Push : *(git push origin addTestDefaultController)*

Maintenant le code est actuellement sur votre **dépôt Github fork** du projet. Il faudra cliquer sur le bouton **Compare & pull request** situé dans l’encadrement jaune.

![GitHub bouton "Compare & pull request"](/public/img/pull-request.png)

Maintenant vous êtes sur la page de la **pull request**, ce qui est **important** avant d’appuyer sur Create pull request :

1.  Choisir la bonne branche sur lequel vous voulez faire votre merge.
2.  Modifier ou créer un message qui permet de déterminer votre pull request.
3.  En bas de la page vous avez les commits et les fichiers modifiés qui vous permettra de vérifier si vous n’avez pas fait quelques bêtises.

## Pour terminer

![GitHub bouton "Compare & pull request"](/public/img/merge-pull-request.jpg)

Dès que vous avez appuyé sur le bouton **Create pull request**, le dépôt officiel Todo & co a **reçu** votre pull request. Vous devez surveiller et **attendre l’acceptation** ou un **retour du responsable**, au cas où une demande de modification est à faire afin de le valider.

Désormais vous savez comment contribuer au projet Todo & co.

![GitHub bouton "Compare & pull request"](/public/img/giphy.gif)

## Processus de qualité

Vous utiliserez Codacy ou CodeClimate pour auditer la qualité du code.

Le code devras suivre certaine régle :
1.  10 fonctions maximum dans une classe et le moins de condition possible (PHPMD),
2.  Pas de duplication de code, factoriser un maximum (PHPCPD),
3.  Respecter les bonnes pratique en matiére de developpement PHP/Symfony (PHPCBF)


