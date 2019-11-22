<?php

/*
 * This file is part of PHP CS Fixer.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *     Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace PhpCsFixer\Console\Output;

use PhpCsFixer\FixerFileProcessedEvent;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Output writer to show the process of a FixCommand.
 *
 * @internal
 */
final class ProcessOutput implements ProcessOutputInterface
{
    /**
     * File statuses map.
     *
     * @var array
     */
    private static $eventStatusMap = array(
        FixerFileProcessedEvent::STATUS_UNKNOWN => array('symbol' => '?', 'format' => '%s', 'description' => 'unknown'),
        FixerFileProcessedEvent::STATUS_INVALID => array('symbol' => 'I', 'format' => '<bg=red>%s</bg=red>', 'description' => 'invalid file syntax, file ignored'),
        FixerFileProcessedEvent::STATUS_SKIPPED => array('symbol' => 'S', 'format' => '<fg=cyan>%s</fg=cyan>', 'description' => 'Skipped'),
        FixerFileProcessedEvent::STATUS_NO_CHANGES => array('symbol' => '.', 'format' => '%s', 'description' => 'no changes'),
        FixerFileProcessedEvent::STATUS_FIXED => array('symbol' => 'F', 'format' => '<fg=green>%s</fg=green>', 'description' => 'fixed'),
        FixerFileProcessedEvent::STATUS_EXCEPTION => array('symbol' => 'E', 'format' => '<bg=red>%s</bg=red>', 'description' => 'error'),
        FixerFileProcessedEvent::STATUS_LINT => array('symbol' => 'E', 'format' => '<bg=red>%s</bg=red>', 'description' => 'error'),
    );

    /**
     * Event dispatcher instance.
     *
     * @var EventDispatcher
     */
    private $eventDispatcher;

    /**
     * Stream output instance.
     *
     * @var OutputInterface
     */
    private $output;

    /**
     * @var int|null
     */
    private $files;

    /**
     * @var int
     */
    private $processedFiles = 0;

    /**
     * @var int|null
     */
    private $symbolsPerLine;

    /**
     * @param OutputInterface $output
     * @param EventDispatcher $dispatcher
     * @param int|null        $nbFiles
     */
    public function __construct(OutputInterface $output, EventDispatcher $dispatcher, $nbFiles)
    {
        $this->output = $output;
        $this->eventDispatcher = $dispatcher;
        $this->eventDispatcher->addListener(FixerFileProcessedEvent::NAME, array($this, 'onFixerFileProcessed'));

        if (null !== $nbFiles) {
            $this->files = $nbFiles;

            //   80               (lines max length)
            // - total length x 2 (e.g. "  1 / 123" => 6 digits and padding spaces)
            // - 11               (extra spaces, parentheses and percentage characters, e.g. " x / x (100%)")
            $this->symbolsPerLine = 80 - strlen((string) $this->files) * 2 - 11;
        }
    }

    public function __destruct()
    {
        $this->eventDispatcher->removeListener(FixerFileProcessedEvent::NAME, array($this, 'onFixerFileProcessed'));
    }

    public function onFixerFileProcessed(FixerFileProcessedEvent $event)
    {
        $status = self::$eventStatusMap[$event->getStatus()];
        $this->output->write($this->output->isDecorated() ? sprintf($status['format'], $status['symbol']) : $status['symbol']);

        if (null !== $this->files) {
            ++$this->processedFiles;
            $symbolsOnCurrentLine = $this->processedFiles % $this->symbolsPerLine;
            $isLast = $this->processedFiles === $this->files;

            if (0 === $symbolsOnCurrentLine || $isLast) {
                $this->output->write(sprintf(
                    '%s %'.strlen((string) $this->files).'d / %d (%3d%%)',
                    $isLast && 0 !== $symbolsOnCurrentLine ? str_repeat(' ', $this->symbolsPerLine - $symbolsOnCurrentLine) : '',
                    $this->processedFiles,
                    $this->files,
                    round($this->processedFiles / $this->files * 100)
                ));

                if (!$isLast) {
                    $this->output->writeln('');
                }
            }
        }
    }

    public function printLegend()
    {
        $symbols = array();

        foreach (self::$eventStatusMap as $status) {
            $symbol = $status['symbol'];
            if ('' === $symbol || isset($symbols[$symbol])) {
                continue;
            }

            $symbols[$symbol] = sprintf('%s-%s', $this->output->isDecorated() ? sprintf($status['format'], $symbol) : $symbol, $status['description']);
        }

        $this->output->write(sprintf("\nLegend: %s\n", implode(', ', $symbols)));
    }
}
