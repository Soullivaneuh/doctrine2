<?php

declare(strict_types=1);

namespace Doctrine\Tests\ORM\Functional\Ticket;

use Doctrine\Tests\Models\CMS\CmsArticle;
use Doctrine\Tests\Models\CMS\CmsUser;
use Doctrine\Tests\OrmFunctionalTestCase;

/**
 * @group DDC-1040
 */
class DDC1040Test extends OrmFunctionalTestCase
{
    public function setUp()
    {
        $this->useModelSet('cms');
        parent::setUp();
    }

    public function testReuseNamedEntityParameter()
    {
        $user = new CmsUser();
        $user->name = "John Galt";
        $user->username = "jgalt";
        $user->status = "inactive";

        $article = new CmsArticle();
        $article->topic = "This is John Galt speaking!";
        $article->text = "Yadda Yadda!";
        $article->setAuthor($user);

        $this->em->persist($user);
        $this->em->persist($article);
        $this->em->flush();

        $dql = "SELECT a FROM Doctrine\Tests\Models\CMS\CmsArticle a WHERE a.user = :author";
        $this->em->createQuery($dql)
                  ->setParameter('author', $user)
                  ->getResult();

        $dql = "SELECT a FROM Doctrine\Tests\Models\CMS\CmsArticle a WHERE a.user = :author AND a.user = :author";
        $this->em->createQuery($dql)
                  ->setParameter('author', $user)
                  ->getResult();

        $dql = "SELECT a FROM Doctrine\Tests\Models\CMS\CmsArticle a WHERE a.topic = :topic AND a.user = :author AND a.user = :author";
        $farticle = $this->em->createQuery($dql)
                  ->setParameter('author', $user)
                  ->setParameter('topic', 'This is John Galt speaking!')
                  ->getSingleResult();

        self::assertSame($article, $farticle);
    }

    public function testUseMultiplePositionalParameters()
    {
        $user = new CmsUser();
        $user->name = "John Galt";
        $user->username = "jgalt";
        $user->status = "inactive";

        $article = new CmsArticle();
        $article->topic = "This is John Galt speaking!";
        $article->text = "Yadda Yadda!";
        $article->setAuthor($user);

        $this->em->persist($user);
        $this->em->persist($article);
        $this->em->flush();

        $dql = "SELECT a FROM Doctrine\Tests\Models\CMS\CmsArticle a WHERE a.topic = ?1 AND a.user = ?2 AND a.user = ?3";
        $farticle = $this->em->createQuery($dql)
                  ->setParameter(1, 'This is John Galt speaking!')
                  ->setParameter(2, $user)
                  ->setParameter(3, $user)
                  ->getSingleResult();

        self::assertSame($article, $farticle);
    }
}
