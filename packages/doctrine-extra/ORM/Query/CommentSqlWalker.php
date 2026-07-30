<?php

namespace Draw\DoctrineExtra\ORM\Query;

use Doctrine\ORM\Query;
use Doctrine\ORM\Query\AST\DeleteStatement;
use Doctrine\ORM\Query\AST\SelectStatement;
use Doctrine\ORM\Query\AST\UpdateStatement;

class CommentSqlWalker extends Query\SqlOutputWalker
{
    public static function addComment(Query $query, string $comment): Query
    {
        $query->setHint(Query::HINT_CUSTOM_OUTPUT_WALKER, self::class);
        $comments = $query->getHint('comment_sql_walker.comments') ?: [];
        $comments[] = $comment;
        $query->setHint('comment_sql_walker.comments', $comments);

        return $query;
    }

    public function getFinalizer(DeleteStatement|UpdateStatement|SelectStatement $AST): Query\Exec\SqlFinalizer
    {
        if ($AST instanceof SelectStatement) {
            return new Query\Exec\SingleSelectSqlFinalizer(
                $this->getQueryWithCalleeComment($this->createSqlForFinalizer($AST))
            );
        }

        return parent::getFinalizer($AST);
    }

    public function walkUpdateStatement(UpdateStatement $updateStatement): string
    {
        return $this->getQueryWithCalleeComment(parent::walkUpdateStatement($updateStatement));
    }

    public function walkDeleteStatement(DeleteStatement $deleteStatement): string
    {
        return $this->getQueryWithCalleeComment(parent::walkDeleteStatement($deleteStatement));
    }

    private function getQueryWithCalleeComment(string $query): string
    {
        $result = '';
        foreach ($this->getQuery()->getHint('comment_sql_walker.comments') as $comment) {
            $result .= '-- '.$comment.\PHP_EOL;
        }

        return $result.$query;
    }
}
