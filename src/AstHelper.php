<?php 

namespace DependencyTracker;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\NodeVisitor;

class AstHelper
{
    public static function findClassLikeNodes($nodes)
    {
        $collectedNodes = [];

        foreach ($nodes as $i => &$node) {
            if ($node instanceof Node\Stmt\ClassLike) {
                $collectedNodes[] = $node;
            } elseif ($node instanceof Use_) {
                continue;
            } elseif (is_array($node)) {
                $collectedNodes = array_merge(static::findClassLikeNodes($node), $collectedNodes);
            } elseif ($node instanceof Node) {
                $collectedNodes = array_merge(static::findClassLikeNodes(
                    static::getSubNodes($node)
                ), $collectedNodes);
            }
        }

        return $collectedNodes;
    }

    /**
     * @param Node\Stmt\ClassLike $klass
     * @return array string
     */
    public static function findInheritances(Node\Stmt\ClassLike $klass)
    {
        $buffer = [];

        if ($klass instanceof Class_ && $klass->namespacedName instanceof Name) {

            if ($klass->extends instanceof Name) {
                $buffer[] = $klass->extends->toString();
            }

            if (!empty($klass->implements)) {
                foreach ($klass->implements as $impl) {

                    if (!$impl instanceof Name) {
                        continue;
                    }

                    $buffer[] = $impl->toString();
                }
            }
        }

        if ($klass instanceof Interface_ && $klass->namespacedName instanceof Name) {
            foreach ($klass->extends as $extends) {
                $buffer[] = $extends->toString();
            }
        }

        return $buffer;
    }

    private static function getSubNodes(Node $node)
    {
        $subnodes = [];
        foreach ($node->getSubNodeNames() as $name) {
            $subnodes[] =& $node->$name;
        }
        return $subnodes;
    }
}