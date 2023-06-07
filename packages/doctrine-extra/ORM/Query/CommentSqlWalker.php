<?php

namespace Draw\DoctrineExtra\ORM\Query;

use Doctrine\ORM\Query;
use Doctrine\ORM\Query\AST\DeleteStatement;
use Doctrine\ORM\Query\AST\SelectStatement;
use Doctrine\ORM\Query\AST\UpdateStatement;
use Doctrine\ORM\Query\SqlWalker;

class CommentSqlWalker extends SqlWalker
{
    public static function addComment(Query $query, string $comment): Query
    {
        $query->setHint(Query::HINT_CUSTOM_OUTPUT_WALKER, self::class);
        $comments = $query->getHint('comment_sql_walker.comments') ?: [];
        $comments[] = $comment;
        $query->setHint('comment_sql_walker.comments', $comments);

        return $query;
    }

    public function walkSelectStatement(SelectStatement $AST): string
    {
        return $this->getQueryWithCalleeComment(parent::walkSelectStatement($AST));
    }

    public function walkUpdateStatement(UpdateStatement $AST): string
    {
        return $this->getQueryWithCalleeComment(parent::walkUpdateStatement($AST));
    }

    public function walkDeleteStatement(DeleteStatement $AST): string
    {
        return $this->getQueryWithCalleeComment(parent::walkDeleteStatement($AST));
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
