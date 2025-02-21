<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Style\SymfonyStyle;
use App\Entity\Sale;

#[AsCommand(
  name: 'sale:get:range'
)]
class GetSalesCommand extends Command
{
    private EntityManagerInterface $em;
    private $client;
    private $thebrandApiBase;

    public function __construct(EntityManagerInterface $em, HttpClientInterface $client, $thebrandApiBase)
    {
        $this->thebrandApiBase = $thebrandApiBase;

        parent::__construct();

        $this->em = $em;
        $this->client = $client;
    }
  
    protected function configure(): void
    {
        $this
            ->addArgument('start', InputArgument::REQUIRED, 'Start, 2023-01-01T00:00:00')
            ->addArgument('end', InputArgument::REQUIRED, 'End, 2023-12-31T00:00:00')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $visitRepo = $this->em->getRepository('App\Entity\Visit');
        $saleRepo = $this->em->getRepository('App\Entity\Sale');
        $io = new SymfonyStyle($input, $output);
        $nStored = 0;

        // Determine range
        $start = $input->getArgument('start');
        $end = $input->getArgument('end');
        if (!$start || !$end) {
            $io->error('Start and End are required');
            return Command::FAILURE;
        }

        // Get Sales from Thebrand API
        $sourceSales = $this->client->request(
            'GET',
            $this->thebrandApiBase . '/get-sales?start=' . $start . '&end=' . $end
        )->toArray();

        // Store Sales
        foreach ($sourceSales as $sourceSale) {
            // Check Visit exists
            $visit = $visitRepo->find($sourceSale['ambRef']);
            if (!$visit) {
                continue;
            }

            // Check Reference not-exists
            if ($saleRepo->count(['reference' => $sourceSale['reference']])) {
                continue;
            }

            // Store
            $sale = new Sale();
            $sale->setVenueName($sourceSale['vName']);
            $sale->setEventName($sourceSale['eName']);
            $sale->setDate(\DateTimeImmutable::createFromFormat(
                'Y-m-d H:i:s',
                $sourceSale['date'] . '00:00:00'
            ));
            $sale->setNumTickets($sourceSale['num']);
            $sale->setBuyDate(\DateTimeImmutable::createFromFormat(
                'Y-m-d H:i:s',
                $sourceSale['createdAt']
            ));
            $sale->setReference($sourceSale['reference']);
            $sale->setVisit($visit);
            $saleRepo->save($sale, true);

            $nStored++;
        }
    
        // Success
        $io->writeln('');
        $io->info($nStored .' of ' . count($sourceSales) . ' processed.');
        $io->success('Success Madafaka!');

        return Command::SUCCESS;
    }
}
