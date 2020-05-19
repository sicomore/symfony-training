<?php

namespace App\Command;

use App\Entity\CodePostal;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;

class ImportPostalCodeCommand extends Command
{
    private $codeInsee;
    private $nom;
    private $codePostal;
    private $gps;

    protected static $defaultName = 'app:import-postal-codes';

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var Filesystem */
    private $filesystem;

    /**
     * ImportPostalCodeCommand constructor.
     */
    public function __construct(EntityManagerInterface $entityManager, Filesystem $filesystem)
    {
        $this->entityManager = $entityManager;
        $this->filesystem = $filesystem;
        parent::__construct();
    }


    protected function configure()
    {
        $this
            ->setDescription('Imports postal codes from CSV file')
//            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
//            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->writeln('Début de l\'import ...');
        $location = 'src/Resources/fixtures/data/laposte_hexasmal.csv';

        if ($this->filesystem->exists($location)) {
            $fileLength = count(file($location));
            $openedFile = fopen($location, 'r');

            $i = 0;
            $index = [];
            $io->progressStart($fileLength);
            while ($data = fgetcsv($openedFile, 1000, ';')) {
                $i++;

                if ($i <= 1) {
                    foreach ($data as $key => $datum) {
                        switch ($datum) {
                            case 'Code_commune_INSEE': $this->codeInsee = $key;
//                            case 'Code_commune_INSEE': $index['Code_commune_INSEE'] = $key;
                            break;
                            case 'Nom_commune': $this->nom = $key;
                            break;
                            case 'Code_postal': $this->codePostal = $key;
                            break;
                            case 'coordonnees_gps': $this->gps = $key;
                            break;
                        }
                    }

                    continue;
                }

//                dump($data);
                $codePostal = (new CodePostal())
                    ->setNom($data[$this->nom])
                    ->setCp($data[$this->codePostal])
                    ->setInsee($data[$this->codeInsee])
                    ->setGps($data[$this->gps])
                ;

                $this->entityManager->persist($codePostal);


                if ($i%50 === 0) {
                    $this->entityManager->flush();
                    $this->entityManager->clear();
                }

                if ($i%500 === 0) {
                    $io->progressAdvance();
                }
            }

            $io->progressFinish();
            $io->success('Le fichier csv a été entièrement importé avec succès.');
        }
        return 0;
    }
}
