<?php

/*
 * This file is part of the GraphAware Bolt package.
 *
 * (c) Graph Aware Limited <http://graphaware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PTS\Bolt\Result;

use PTS\Bolt\PackStream\Structure\Structure;
use PTS\Bolt\Record\RecordView;
use PTS\Bolt\Result\Type\Node;
use PTS\Bolt\Result\Type\Path;
use PTS\Bolt\Type\Point2D;
use PTS\Bolt\Type\Point3D;
use PTS\Bolt\Result\Type\Relationship;
use PTS\Bolt\Result\Type\UnboundRelationship;
use PTS\Bolt\Type\Temporal\Date;
use PTS\Bolt\Type\Temporal\DateTimeOffset;
use PTS\Bolt\Type\Temporal\DateTimeZoned;
use PTS\Bolt\Type\Temporal\Duration;
use PTS\Bolt\Type\Temporal\LocalDateTime;
use PTS\Bolt\Type\Temporal\LocalTime;
use PTS\Bolt\Type\Temporal\Time;
use GraphAware\Common\Cypher\StatementInterface;
use GraphAware\Common\Result\AbstractRecordCursor;
use RuntimeException;

class Result extends AbstractRecordCursor
{
    /**
     * @var RecordView[]
     */
    protected $records = [];

    /**
     * @var array
     */
    protected $fields;

    /**
     * {@inheritdoc}
     */
    public function __construct(StatementInterface $statement)
    {
        $this->resultSummary = new ResultSummary($statement);

        parent::__construct($statement);
    }

    /**
     * @param Structure $structure
     */
    public function pushRecord(Structure $structure)
    {
        $elts = $this->arrayMapDeep($structure->getElements());
        $this->records[] = new RecordView($this->fields, $elts);
    }

    /**
     * @return RecordView[]
     */
    public function getRecords()
    {
        return $this->records;
    }

    /**
     * @return RecordView
     *
     * @throws RuntimeException When there is no record.
     */
    public function getRecord()
    {
        if (count($this->records) < 1) {
            throw new RuntimeException('There is no record');
        }

        return $this->records[0];
    }

    /**
     * @param array $fields
     */
    public function setFields(array $fields)
    {
        $this->fields = $fields['fields'];
    }

    /**
     * @param array $stats
     */
    public function setStatistics(array $stats)
    {
        $this->resultSummary->setStatistics($stats);
    }

    /**
     * @param $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return ResultSummary
     */
    public function summarize()
    {
        return $this->resultSummary;
    }

    public function position()
    {
        // TODO: Implement position() method.
    }

    public function skip()
    {
        // TODO: Implement skip() method.
    }

    private function arrayMapDeep(array $array)
    {
        foreach ($array as $k => $v) {
            if ($v instanceof Structure) {
                $elts = $v->getElements();
                switch ($v->getSignature()) {
                    case Structure::SIGNATURE_NODE:
                        $array[$k] = new Node($elts[0], $elts[1], $elts[2]);
                        break;
                    case Structure::SIGNATURE_RELATIONSHIP:
                        $array[$k] = new Relationship($elts[0], $elts[1], $elts[2], $elts[3], $elts[4]);
                        break;
                    case Structure::SIGNATURE_UNBOUND_RELATIONSHIP:
                        $array[$k] = new UnboundRelationship($elts[0], $elts[1], $elts[2]);
                        break;
                    case Structure::SIGNATURE_PATH:
                        $array[$k] = new Path(
                            $this->arrayMapDeep($elts[0]),
                            $this->arrayMapDeep($elts[1]),
                            $this->arrayMapDeep($elts[2])
                        );
                        break;
                    case Structure::SIGNATURE_POINT3D:
                        $array[$k] = new Point3D($elts[1], $elts[2], $elts[3], $elts[0]);
                        break;
                    case Structure::SIGNATURE_POINT2D:
                        $array[$k] = new Point2D($elts[1], $elts[2], $elts[0]);
                        break;
                    case Structure::SIGNATURE_DATE_TIME_OFFSET:
                        $array[$k] = new DateTimeOffset($elts[0], $elts[1], $elts[2]);
                        break;
                    case Structure::SIGNATURE_DATE_TIME_ZONED:
                        $array[$k] = new DateTimeZoned($elts[0], $elts[1], $elts[2]);
                        break;
                    case Structure::SIGNATURE_LOCAL_DATE_TIME:
                        $array[$k] = new LocalDateTime($elts[0], $elts[1]);
                        break;
                    case Structure::SIGNATURE_LOCAL_TIME:
                        $array[$k] = new LocalTime($elts[0]);
                        break;
                    case Structure::SIGNATURE_TIME:
                        $array[$k] = new Time($elts[0], $elts[1]);
                        break;
                    case Structure::SIGNATURE_DURATION:
                        $array[$k] = new Duration($elts[0], $elts[1], $elts[2], $elts[3]);
                        break;
                    case Structure::SIGNATURE_DATE:
                        $array[$k] = new Date($elts[0]);
                        break;
                    default:
                        $array[$k] = $this->arrayMapDeep($v->getElements());
                }
            } elseif (is_array($v)) {
                $array[$k] = $this->arrayMapDeep($v);
            }
        }

        return $array;
    }

    /**
     * {@inheritdoc}
     */
    public function size()
    {
        return count($this->records);
    }

    /**
     * @return RecordView
     * @throws \RuntimeException When there is no record
     */
    public function firstRecord()
    {
        if (!empty($this->records)) {
            return $this->records[0];
        }

        throw new RuntimeException('There is no record');
    }

    /**
     * {@inheritdoc}
     */
    public function firstRecordOrDefault($default)
    {
        if (0 === $this->size()) {
            return $default;
        }

        return $this->firstRecord();
    }
}
