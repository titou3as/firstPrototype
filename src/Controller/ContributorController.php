<?php

namespace App\Controller;

use App\Entity\Contributor;
use App\Form\ConnexionContributorType;
use App\Form\ContributorType;
use App\Repository\ContributorRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/contributor")
 */
class ContributorController extends AbstractController
{
    /**
     * @Route("/contributor", name="contributor_index", methods={"GET"})
     */
    public function index(ContributorRepository $contributorRepository): Response
    {
        return $this->render('contributor/index.html.twig', [
            'contributors' => $contributorRepository->findAll(),
        ]);
    }

    /**
     * @Route("contributor/new", name="contributor_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $contributor = new Contributor();
        $form = $this->createForm(ContributorType::class, $contributor);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($contributor);
            $entityManager->flush();
            $this->addFlash('success','Votre compte est crée');
            return $this->redirectToRoute('contributor_index');
        }

        return $this->render('contributor/new.html.twig', [
            'contributor' => $contributor,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/contributor/{id}", name="contributor_show", methods={"GET"})
     */
    public function show(Contributor $contributor): Response
    {
        return $this->render('contributor/show.html.twig', [
            'contributor' => $contributor,
        ]);
    }

    /**
     * @Route("/contributor/{id}/edit", name="contributor_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Contributor $contributor): Response
    {
        $form = $this->createForm(ContributorType::class, $contributor);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success','Votre compte est mis à jour');
            return $this->redirectToRoute('contributor_index', [
                'id' => $contributor->getId(),
            ]);
        }

        return $this->render('contributor/edit.html.twig', [
            'contributor' => $contributor,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/contributor/{id}", name="contributor_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Contributor $contributor): Response
    {
        if ($this->isCsrfTokenValid('delete'.$contributor->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($contributor);
            $entityManager->flush();
            $this->addFlash('success','Votre compte est supprimé');
        }

        return $this->redirectToRoute('contributor_index');
    }

    /**
     * @Route("/contributor/login", name = "contributor_connexion")
     */
    public function connexion(ContributorRepository $repository, Request $request){

        $form = $this->createForm(ConnexionContributorType::class);
        $form->handleRequest($request);
        if($form->isSubmitted()&& $form->isValid()){
            $data = $form->getData();
            dump($data);
            $login = $data->getLogin();
            $pwd = $data->getPwd();
            /**
             *Searching for contributor's having login && Pwd into the database
             * @var $contributor Contributor
             */
            $contributor = $repository->findOneBy([
                'login' => $login,
                'pwd'   => $pwd
            ]);
            dump($contributor);
            if($contributor!==null){
                $this->addFlash('success','Authentification réussite');
                return $this->redirectToRoute('contributor_show',['id'=> $contributor->getId()]);
            }
        }
        return $this->render('/contributor/connexion.html.twig',[
            'form' => $form->createView(),
        ]);
    }
    /**
     * @Route("/contributor/{id}", name ="contributor_validation_connexion")
     * @return Response
     */
    public function validation($id) : Response
    {
        $contributor = $this->getDoctrine()
                            ->getRepository(Contributor::class)
                            ->find($id);
        if(!$contributor){
            return $this->render('contributor/error.html.twig');
        }
        return $this->render('contributor/index.html.twig', [
            'controller_name' => 'ContributorController',
            'contributor' => $contributor,
        ]);
    }
}
