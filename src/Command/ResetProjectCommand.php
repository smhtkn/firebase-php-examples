<?php

namespace App\Command;

use Kreait\Firebase\Auth;
use Kreait\Firebase\Database;
use Kreait\Firebase\Database\RuleSet;
use Kreait\Firebase\Storage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ResetProjectCommand extends Command
{
    protected static $defaultName = 'app:reset-project';

    /** @var Auth */
    private $auth;

    /** @var Database */
    private $database;

    /** @var Storage */
    private $storage;

    public function __construct(Auth $auth, Database $database, Storage $storage)
    {
        parent::__construct();

        $this->auth = $auth;
        $this->database = $database;
        $this->storage = $storage;
    }

    protected function configure()
    {
        $this
            ->setDescription('Reset parts of a Firebase project to its initial state')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        if ($io->confirm('Reset database rules?', false)) {
            $this->database->updateRules(RuleSet::default());
            $io->success('Done!');
        }

        if ($io->confirm('Empty realtime database?', false)) {
            $this->database->getReference('/')->remove();
            $io->success('Done!');
        }

        if ($io->confirm('Empty cloud storage?', false)) {
            foreach ($this->storage->getBucket()->objects() as $object) {
                $object->delete();
            }
            $io->success('Done!');
        }

        if ($io->confirm('Delete all users?', false)) {
            foreach ($this->auth->listUsers() as $user) {
                $this->auth->deleteUser($user->uid);
            }
            $io->success('Done!');
        }

        return 0;
    }
}
