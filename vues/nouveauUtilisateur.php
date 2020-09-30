        <div class="bouteille" ><h4><span>Créez un compte<?php echo $bouteille['nom']?></span></h4>
            <div>
                <form id="nutilisateur" action="" method="post">
                    <?php if(!empty($message)):?>
                    <p><?php echo $message;?></p>
                    <?php endif; ?>
                    <label>Nom</label>
                    <p><input class="inputForm" name="nom" placeholder="Nom" required> </p>
                    <p><?= isset($erreurs['nom']) ? $erreurs['nom'] : "" ?></p>
                    <label>Prénom</label>
                    <p><input class="inputForm" name="prenom" placeholder="Prénom" required></p>
                    <p><?= isset($erreurs['prenom']) ? $erreurs['prenom'] : "" ?></p>
                    <label>Identifiant</label>
                    <p><input class="inputForm" name="identifiant" placeholder="Identifiant" required> </p>
                    <p><?= isset($erreurs['identifiant']) ? $erreurs['identifiant'] : "" ?></p>
                    <label>Mot de passe</label>
                    <p><input class="inputForm" type="password" name="mdp" placeholder="Mot de passe" required></p>
                    <p><?= isset($erreurs['mdp']) ? $erreurs['mdp'] : "" ?></p>
                    <label>Email</label>
                    <p><input class="inputForm" type="email" name="courriel" placeholder="Email" required> </p>
                    <p><?= isset($erreurs['courriel']) ? $erreurs['courriel'] : "" ?></p>
                    <label>Téléphone</label>
                    <p><input class="inputForm" name="telephone" placeholder="Téléphone" required></p>
                    <p><?= isset($erreurs['telephone']) ? $erreurs['telephone'] : "" ?></p>

                     <p class="mdp"><button type="submit" class="creationButton" name="creer">Créez</button>
                        Si vous avez déjà un compte <a href="index.php"> Identifiez-vous </a></p>
                </form>       
        </div>
    </div>
   

   