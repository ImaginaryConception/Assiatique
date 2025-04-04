<?php

namespace App\Controller;

use DateTime;
use App\Entity\User;
use App\Entity\Video;
use App\Entity\VideoHind;
use App\Form\AddVideoHindFormType;
use App\Form\AddVideoAssiaFormType;
use App\Form\RequestEditAssiaFormType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class MainController extends AbstractController
{

    #[Route('/', name: 'home')]
    public function home(ManagerRegistry $doctrine): Response
    {

        $repository = $doctrine->getRepository(User::class);

        $repository2 = $doctrine->getRepository(Video::class);

        $repository3 = $doctrine->getRepository(VideoHind::class);

        $users = $repository->findAll();

        $videos = $repository2->findAll();

        $videosHind = $repository3->findAll();

        $countUsers = count($users);

        return $this->render('main/home.html.twig', [
            'countUsers' => $countUsers,
            'videos' => $videos,
            'videosHind' => $videosHind,
        ]);
    }

    #[Route('/modifier/{id}/', name: 'edit', priority: 10)]
    #[ParamConverter('video', options: ['mapping' => ['id' => 'id']])]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(Video $video, Request $request, ManagerRegistry $doctrine): Response
    {

        if($this->getUser()->getIdentifiant() == 'assia'){

            $form = $this->createForm(AddVideoAssiaFormType::class, $video);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {

                $em = $doctrine->getManager();
                $em->flush();

                $this->addFlash('success', 'Les modifications ont bien été prises en compte.');

                return $this->redirectToRoute('assia_videos_list');
            }

            return $this->render('main/add_video.html.twig', [
                'add_video_form' => $form->createView(),
            ]);
        }

        return $this->redirectToRoute('edit_hind');
    }

    #[Route('/supprimer/{id}/', name: 'remove', priority: 10)]
    #[ParamConverter('video', options: ['mapping' => ['id' => 'id']])]
    #[IsGranted('ROLE_ADMIN')]
    public function remove(Video $video, ManagerRegistry $doctrine): Response
    {

        if($this->getUser()->getIdentifiant() == 'assia'){

            $em = $doctrine->getManager();
            $em->remove($video);
            $em->flush();

            $this->addFlash('success', 'Supprimé avec succès.');

            return $this->redirectToRoute('assia_videos_list');

        }

        return $this->redirectToRoute('remove_hind');
    }

    #[Route('/modifier-h/{id}/{id2}/', name: 'edit_hind', priority: 10)]
    #[ParamConverter('video', options: ['mapping' => ['id' => 'id']])]
    #[ParamConverter('videoHind', options: ['mapping' => ['id2' => 'id']])]
    #[IsGranted('ROLE_ADMIN')]
    public function editHind(VideoHind $videoHind, Video $video, Request $request, ManagerRegistry $doctrine): Response
    {

        if($this->getUser()->getIdentifiant() == 'hind'){

            $form = $this->createForm(AddVideoHindFormType::class, $videoHind);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {

                $em = $doctrine->getManager();
                $video->setStatut('editrequestconfirmed');
                $videoHind->setStatut('editrequestconfirmed');
                $em->flush();

                $this->addFlash('success', 'Les modifications ont bien été prises en compte.');

                return $this->redirectToRoute('hind_videos_list');
            }

            return $this->render('main/add_video_h.html.twig', [
                'add_video_h_form' => $form->createView(),
            ]);
        }

        return $this->redirectToRoute('edit');
    }

    #[Route('/modifier-a/{id}/{id2}/', name: 'edit_assia', priority: 10)]
    #[ParamConverter('videoHind', options: ['mapping' => ['id' => 'id']])]
    #[ParamConverter('video', options: ['mapping' => ['id2' => 'id']])]
    #[IsGranted('ROLE_ADMIN')]
    public function editAssia(Video $video, VideoHind $videoHind, Request $request, ManagerRegistry $doctrine): Response
    {

        if($this->getUser()->getIdentifiant() == 'assia'){

            $form = $this->createForm(RequestEditAssiaFormType::class, $video);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {

                $em = $doctrine->getManager();
                $video->setStatut('editrequest');
                $videoHind->setStatut('editrequest');
                $em->flush();

                $this->addFlash('success', 'La demande de modification a bien été envoyée.');

                return $this->redirectToRoute('home');
            }

            return $this->render('main/add_video_assia.html.twig', [
                'add_video_assia_form' => $form->createView(),
            ]);
        }

        return $this->redirectToRoute('edit');
    }

    #[Route('/supprimer-h/{id}/', name: 'remove_hind', priority: 10)]
    #[ParamConverter('videoHind', options: ['mapping' => ['id' => 'id']])]
    #[IsGranted('ROLE_ADMIN')]
    public function removeHind(VideoHind $video, ManagerRegistry $doctrine): Response
    {

        if($this->getUser()->getIdentifiant() == 'hind'){

            $em = $doctrine->getManager();
            $em->remove($video);
            $em->flush();

            $this->addFlash('success', 'Supprimé avec succès.');

            return $this->redirectToRoute('hind_videos_list');
        }

        return $this->redirectToRoute('remove');
    }

    #[Route('/supprimer-a/{id}/{id2}', name: 'remove_assia', priority: 10)]
    #[ParamConverter('videoHind', options: ['mapping' => ['id' => 'id']])]
    #[ParamConverter('video', options: ['mapping' => ['id2' => 'id']])]
    #[IsGranted('ROLE_ADMIN')]
    public function removeAssia(VideoHind $videoHind, Video $video, ManagerRegistry $doctrine): Response
    {

        if($this->getUser()->getIdentifiant() == 'assia'){

            $em = $doctrine->getManager();
            $em->remove($video);
            $em->remove($videoHind);
            $em->flush();

            $this->addFlash('success', 'Confirmé avec succès.');

            return $this->redirectToRoute('home');
        }

        return $this->redirectToRoute('home');
    }

    #[Route('/videos-envoyees/', name: 'assia_videos_list')]
    public function assiaVideosList(ManagerRegistry $doctrine): Response
    {

        if($this->getUser()->getIdentifiant() == 'assia'){

            $repository = $doctrine->getRepository(Video::class);

            $videos = $repository->findAll();

            return $this->render('main/videos_list_assia.html.twig', [
                'videos' => $videos,
            ]);
        }

        return $this->redirectToRoute('hind_videos_list');
    }

    #[Route('/videos-envoyees-h/', name: 'hind_videos_list')]
    public function hindVideosList(ManagerRegistry $doctrine): Response
    {

        if($this->getUser()->getIdentifiant() == 'hind'){

            $repository = $doctrine->getRepository(VideoHind::class);

            $videos = $repository->findAll();

            return $this->render('main/videos_list_hind.html.twig', [
                'videos' => $videos,
            ]);
        }

        return $this->redirectToRoute('assia_videos_list');
    }

    #[Route('/mentions-legales/', name: 'mentions_legales')]
    #[IsGranted('ROLE_ADMIN')]
    public function mentionsLegales(): Response
    {
        return $this->render('main/mentions_legales.html.twig');
    }

    #[Route('/ajouter-une-video/', name: 'add_video')]
    public function addVideo(Request $request, ManagerRegistry $doctrine): Response
    {

        if($this->getUser()->getIdentifiant() == 'assia'){

            $video = new Video();

            $form = $this->createForm(AddVideoAssiaFormType::class, $video);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {

                $em = $doctrine->getManager();

                if (!$form->get('video')->getData() == null) {

                    $thevideo = $form->get('video')->getData();

                    $newFileName = md5(time() . rand() . uniqid()) . '.' . $thevideo->guessExtension();

                    $video
                        ->setDatetime(new DateTime())
                        ->setVideo($newFileName)
                    ;

                    $thevideo->move(
                        $this->getParameter('app.user.video.directory'),
                        $newFileName
                    );

                }

                $em->persist($video);

                $em->flush();

                $this->addFlash('success', 'Fichier envoyé avec succès.');

                return $this->redirectToRoute('add_video');
            }

            return $this->render('main/add_video.html.twig', [
                'add_video_form' => $form->createView(),
            ]);
        }

        return $this->redirectToRoute('add_video_hind');
    }

    #[Route('/ajouter-une-video-h/', name: 'add_video_hind')]
    public function addVideoHind(Request $request, ManagerRegistry $doctrine): Response
    {

        if($this->getUser()->getIdentifiant() == 'hind'){

            $video = new VideoHind();

            $form = $this->createForm(AddVideoHindFormType::class, $video);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {

                $em = $doctrine->getManager();

                if (!$form->get('video')->getData() == null) {

                    $thevideo = $form->get('video')->getData();

                    $newFileName = md5(time() . rand() . uniqid()) . '.' . $thevideo->guessExtension();

                    $video
                        ->setDatetime(new DateTime())
                        ->setVideo($newFileName)
                    ;

                    $thevideo->move(
                        $this->getParameter('app.user.videohind.directory'),
                        $newFileName
                    );

                }

                $em->persist($video);

                $em->flush();

                $this->addFlash('success', 'Fichier envoyé avec succès.');

                return $this->redirectToRoute('add_video_hind');
            }

            return $this->render('main/add_video_hind.html.twig', [
                'add_video_form' => $form->createView(),
            ]);
        }

        return $this->redirectToRoute('add_video');
    }
}