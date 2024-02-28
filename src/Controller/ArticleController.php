<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\SecurityBundle\Security;


class ArticleController extends AbstractController
{
    #[Route('/', name: 'app_article')]
    public function index(EntityManagerInterface $em, Request $r, SluggerInterface $slugger, Security $security): Response
    {
        $un_article = new Article();
        $form = $this->createForm(ArticleType::class, $un_article);

        $form->handleRequest($r);

        if($form->isSubmitted() && $form->isValid()){
            $slug = $slugger->slug($un_article->getTitle());
            $un_article->setSlug($slug);
            $em->persist($un_article);
            $em->flush();
        }

        $articles = $em->getRepository(Article::class)->findAll();

        $user = $security->getUser();

        if (null !== $user) {
            $roles = $user->getRoles();
        } else {
            $roles = [];
        }

        return $this->render('article/index.html.twig', [
            'articles' => $articles,
            'form' => $form->createView(),
            'userRole' => $roles
        ]);
    }

    #[Route('/delete/{id}', name:'app_article_delete')]
    public function delete(Request $r, EntityManagerInterface $em, Article $article){
        if($this->isCsrfTokenValid('delete'.$article->getId(), $r->request->get('csrf'))){
            $em->remove($article);
            $em->flush();
        }

        return $this->redirectToRoute(('app_article'));
    }

    #[Route('/article/{slug}', name:'app_article_show')]
public function show(Article $article)
    {
        return $this->render('article/show.html.twig', [
            'article' => $article
        ]);
    }
}
