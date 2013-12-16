## Coverage Checker ##

**Coverage Checker** permet de gérer les builds Jenkins sous certaines conditions.

### Existant ###

**Marco Pivetta** avait concocté [coverage checker](http://ocramius.github.io/blog/automated-code-coverage-check-for-github-pull-requests-with-travis/). Cependant cet outil ne nous permet pas de gérer l'architecture multi module. Or dans les projets d'aujourd'hui nous utilisons principalement des bundles (symfony2) ou des modules (zend Framework 2).
C'est pourquoi nous avons modifié le coverage checker pour prendre en compte l'architecture multi-module

### Rappel du Fonctionnement du coverage checker ###

Dans le build de jenkins, nous appelons le coverage checker qui prendra deux paramètres :

- le module qui doit être testé
- le taux de couverture minimum

Si le taux de couverture minimum est supérieur au taux de couverture réalisé par les tests (dans le cas où il n’y a pas assez de tests), alors il enverra un message d’erreur à Jenkins. Nous avons décidé que cela arrêtera le build Jenkins et donc le déploiement sur notre serveur d’intégration.

Si le taux de couverture minimum est inférieur au taux de couverture réalisé par les tests (dans le cas où tout est bien testé), alors il enverra un message de succès à Jenkins.


### Modification du coverage checker ###

Nous avons donc dû prendre en compte la notion du multi-module. Ainsi, nous avons créé un fichier xml qui décrit l’ensemble des modules et la limite à atteindre pour la couverture des tests de chaque module.

```
<?xml version="1.0" encoding="UTF-8"?>
<project>
<modules>
      <module>
        <name>user</name>
        <path>vendor/adfab/user</path>
        <limit>10</limit>
        <mandatory>false</mandatory>
      </module>
...
```
[Voir le modules.xml](https://github.com/AdFabConnect/coverage-checker/build/modules.xml)

- Le nom correspond au nom du module
- Le path correspond au path du module
- Le limit correspond au pourcentage mimimun du taux de couverture des tests
- Le mandatory permet de dire si le taux de couverture du module est bloquant ou pas pour le build jenkins

Notre coverage checker va récupérer ce fichier xml et regardera dans chaque module si le pourcentage des tests est supérieur à la limite fixée dans le xml pour ce module.
De la même manière que le fonctionnement par défaut, si pour un des modules, le pourcentage de couverture est inférieur à la limite fixée dans le xml pour le module, alors le coverage checker va informer Jenkins qu’il y a une erreur.

En plus de cela, nous avons ajouté des fonctionnalités complémentaires :

- une vue en HTML d’un tableau récapitulent les différents modules avec leurs taux de couverture et leurs limites. 
- une vue dans la console avec les différents modules et leurs taux de couvertures.


### Utilisation ###

La partie HTML va être utilisée par Jenkins. Le rapport est en effet sauvegardé au format HTML dans le même répertoire que coverage-checker. Dans le job Jenkins, il suffira alors d’ajouter un nouveau rapport html.

Nous avons pour notre part utilisé une config Ant afin de permettre de lancer l’intégralité des tests unitaires de chacun des modules puis d’enchainer sur le coverage-checker.

```
<target name="coverage-checker" depends="phpunit">
    <exec dir="/usr/bin" executable="php" failonerror="true" description="check unit test coverage percentage">
    <arg line="${basedir}/build/coverage-checker.php" />
    </exec>
</target>
```
