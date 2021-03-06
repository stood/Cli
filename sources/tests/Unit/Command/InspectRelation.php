<?php
/*
 * This file is part of Pomm's Cli package.
 *
 * (c) 2014 - 2015 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Cli\Test\Unit\Command;

use PommProject\Cli\Test\Fixture\StructureFixtureClient;
use PommProject\Foundation\Session\Session;
use PommProject\ModelManager\Tester\ModelSessionAtoum;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class InspectRelation extends ModelSessionAtoum
{
    protected function initializeSession(Session $session)
    {
        $session
            ->registerClient(new StructureFixtureClient())
            ;
    }

    public function testExecute()
    {
        $application = new Application();
        $application->add($this->newTestedInstance()->setSession($this->buildSession()));
        $command = $application->find('pomm:inspect:relation');
        $tester = new CommandTester($command);
        $tester->execute(
            [
                'command'          => $command->getName(),
                'config-name'      => 'pomm_test',
                'schema'           => 'pomm_test',
                'relation'         => 'beta',
            ],
            [
                'decorated' => false
            ]
        );
        $this
            ->string($tester->getDisplay())
            ->contains(join(PHP_EOL, [
                "+----+------------+--------------------------+--------------------------------------------------+---------+-------------------------------+",
                "| pk | name       | type                     | default                                          | notnull | comment                       |",
                "+----+------------+--------------------------+--------------------------------------------------+---------+-------------------------------+",
                "| *  | beta_one   | int4                     | nextval('pomm_test.beta_beta_one_seq'::regclass) | yes     | This is the beta.one comment. |",
                "| *  | beta_two   | int4                     |                                                  | yes     |                               |",
                "|    | beta_three | pomm_test.complex_type[] |                                                  | yes     |                               |",
                "+----+------------+--------------------------+--------------------------------------------------+---------+-------------------------------+",
                "",
            ]))
            ->matches('#Relation pomm_test.beta \\(size with indexes\\: [0-9]+ bytes\\)#')
        ;
        $this
            ->exception(function () use ($tester, $command) {
                    $tester->execute(
                        [
                            'command'          => $command->getName(),
                            'config-name'      => 'pomm_test',
                            'schema'           => 'pomm_test',
                            'relation'         => 'whatever',
                        ]
                    );
            })
            ->isInstanceOf('\PommProject\Cli\Exception\CliException')
            ;
    }
}
