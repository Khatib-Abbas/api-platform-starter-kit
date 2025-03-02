<?php

namespace App\Command;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:check-ssl', // Command name accessible via the console.
    description: 'Checks whether the database connection is using SSL.' // Command description.
)]
class SslCheckCommand extends Command
{
    // The Doctrine DBAL connection instance.
    private Connection $connection;

    // The constructor receives the DBAL connection as a dependency.
    public function __construct(Connection $connection)
    {
        // Call the parent constructor.
        parent::__construct();
        $this->connection = $connection;
    }

    /**
     * Executes the command.
     *
     * @throws Exception if the query fails.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Execute the SQL query to get the SSL cipher information.
        $result = $this->connection
            ->executeQuery("SHOW STATUS LIKE 'Ssl_cipher'")
            ->fetchAssociative();

        // Retrieve the cipher value from the result (if exists).
        $sslCipher = $result['Value'] ?? null;

        // Check if a cipher value was returned.
        if ($sslCipher) {
            // Write a message indicating SSL is active and display the cipher used.
            $output->writeln('SSL connection active. Cipher used: ' . $sslCipher);
        } else {
            // Write a message indicating that the SSL connection is not active.
            $output->writeln('SSL connection not active.');
        }

        // Return success status.
        return Command::SUCCESS;
    }
}
