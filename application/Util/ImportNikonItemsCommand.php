<?php

namespace App\Util;

use App\Model\NikonItem;
use Cocur\Slugify\Slugify;

/**
 * Classe pour commande permettant l'import contenus Nikon.
 * Class ImportNikonItemsCommand
 * @package App\Util
 */
class ImportNikonItemsCommand extends Command
{
    protected $name = 'import';
    protected $description = 'Importe les contenus Nikon depuis un fichier XML.';

    /**
     * @param Array $argv
     * @return int
     */
    public function execute($argv)
    {
        $output = parent::execute($argv);
        if(!is_null($output))
        {
            echo $output;
            return 1;
        }

        $invalidArguments = $dryRun = false;

        // Récupération des arguments
        $category = filter_var($argv[2], FILTER_VALIDATE_INT);
        $xmlPath = filter_var($argv[3], FILTER_SANITIZE_STRING, FILTER_FLAG_PATH_REQUIRED);

        if(!$xmlPath || $category === false)
            $invalidArguments = true;

        // Categorie
        if(!($category >= NikonItem::PRODUIT && $category <= NikonItem::PROMOTION))
        {
            $invalidArguments = true;
        }

        if(!$invalidArguments && isset($argv[4]))
        {
            if('--dry-run' == $argv[4])
                $dryRun = true;
            else
                $invalidArguments = true;
        }

        if($invalidArguments)
        {
            echo PHP_EOL . 'ERREUR : Arguments invalides' . PHP_EOL . $this->getHelp();
            return 1;
        }

        echo PHP_EOL . 'Import depuis ' . $xmlPath . ' [...]' . PHP_EOL;
        if($dryRun)
            echo PHP_EOL . '===== MODE DRY-RUN POUR TEST (AUCUNE ECRITURE EN BASE) =====' . PHP_EOL;

        if(file_exists($xmlPath))
        {
            echo PHP_EOL . '> Fichier trouvé...';
            $xml = new \SimpleXMLElement($xmlPath, LIBXML_NOWARNING, true);
            if(!$xml)
            {
                echo PHP_EOL . 'ERREUR : impossible de récupérer le contenu du fichier';
                return 1;
            }

            $row = 1;
            $success = 0;
            db()->beginTransaction();

            echo PHP_EOL . '> Suppression éléments existants...';
            // Suppression éléments existants
            $sth = db()->prepare('DELETE FROM NikonItem WHERE category = :category;');
            $sth->bindValue(':category', $category);
            $sth->execute();

            foreach($xml as $item)
            {
                $imageUrl = null;
                // Récupération de l'image
                if(!is_null($item->image))
                {
                    $imageUrl = (string)$item->image;
                    $slugify = new Slugify();
                    $target = strtolower($slugify->slugify($item->titre, '-') . substr(basename($imageUrl), strrpos(basename($imageUrl), '.')));

                    echo PHP_EOL . '> Ajout image : ' . $target;
                    if(!file_exists('../medias/images/items/' . $target) && !$dryRun)
                    {
                        try
                        {
                            $file = file_get_contents($imageUrl);
                            if($file !== null)
                            {
                                if(file_put_contents('../medias/images/items/' . $target, $file, FILE_USE_INCLUDE_PATH) !== false)
                                    $imageUrl = $target;
                                else
                                    $imageUrl = null;
                            }
                            else
                            {
                                $imageUrl = null;
                            }
                        }
                        catch(\Exception $e)
                        {
                            $imageUrl = null;
                        }
                    }
                    else
                    {
                        $imageUrl = $target;
                    }
                }

                // Sauvegarde en base
                try
                {
                    $nikonItem = new NikonItem(null,
                                               $category,
                                               isset($item['type']) ? (string)$item['type'] : null,
                                               (string)$item->titre,
                                               (string)$item->description,
                                               (string)$item->urlDetails,
                                               isset($item->urlStore) ? (string)$item->urlStore : null,
                                               $imageUrl);
                    $nikonItem->save();
                    $success++;
                }
                catch(\Exception $e)
                {
                    echo PHP_EOL . 'ERREUR : échec de l\'enregistrement pour la ligne :  ' . $item . PHP_EOL . $e->getMessage();
                    if(!$dryRun)
                    {
                        if(db()->rollBack())
                            echo PHP_EOL . 'ROLLBACK : aucune donnée n\'a été importée.';

                        return 1;
                    }
                    continue;
                }

                $row++;
            }
            $row--;

            if($dryRun)
            {
                db()->rollBack();
                echo PHP_EOL . PHP_EOL . '===== MODE DRY-RUN POUR TEST (AUCUNE ECRITURE EN BASE) =====' . PHP_EOL . '...';
            }
            else
                db()->commit();
                echo PHP_EOL . $success . ' / ' . $row . ' contenus importés avec succès.' . PHP_EOL;
        }
        else
        {
            echo PHP_EOL . 'ERREUR : fichier introuvable ou inaccessible en lecture';
            return 1;
        }

        return 0;
    }

    public function getHelp()
    {
        $help = PHP_EOL . $this->description . PHP_EOL;
        $help .= '-------------' . PHP_EOL;
        $help .= 'Utilisation :' . PHP_EOL;
        $help .= '-------------' . PHP_EOL;
        $help .= '    ' . $this->name . ' <category> <chemin_du_fichier_xml> [--dry-run]' . PHP_EOL . PHP_EOL;
        $help .= '<category> : 1 = produits' . PHP_EOL;
        $help .= '             2 = formations' . PHP_EOL;
        $help .= '             3 = articles' . PHP_EOL;
        $help .= '             4 = promotions' . PHP_EOL;
        $help .= 'Utilisez --dry-run pour tester le format du fichier en entrée et simuler l\'import.' . PHP_EOL . PHP_EOL;
        return $help;
    }
}