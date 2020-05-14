<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use App\Repository\PostRepository;
use App\Services\FileUploader;
use App\Services\Notification;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/post", name="post.")
 */
class PostController extends AbstractController
{
    /**
     * @Route("/", name="index")
     * @param PostRepository $postRepository
     */
    public function index(PostRepository $postRepository)
    {
        $posts = $postRepository->findAll();

        // dump($posts); muestra en formato json

        return $this->render('post/index.html.twig', [
            'posts' => $posts
        ]);
    }

    /**
     * @Route("/create", name="create")
     * @param Request $request
     */
    public function create(Request $request, FileUploader $fileUploader, Notification $notification){
        // create a new post with title
        $post = new Post();

        $form = $this->createForm(PostType::class, $post);

        $form->handleRequest($request);
        //$form->getErrors(); //explicado video hora 2:09:15
        if($form->isSubmitted()){ // && $form->isValid()){ // explicado video hora 2:08:26
            //  entity manager
            $em = $this->getDoctrine()->getManager();

            /** @var UploadedFile $file */
            $file = $request->files->get('post')['attachment'];
            if($file){
                $filename = $fileUploader->uploadFle($file);

                $post->setImage($filename);
            }
            $em->persist($post);
            $em->flush();

            return $this->redirect($this->generateUrl('post.index'));

        }


        return $this->render('post/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/show/{id}", name="show")
     * @param $id
     * @param PostRepository $postRepository
     * @return Response
     */
    public function show($id, PostRepository $postRepository){
         // a la hora 1:42:40 explicar como cambiar los parámetros pero no me funcionó
        $post = $postRepository->find($id);
        //dump($post); die;

        // create the show view
        return $this->render('post/show.html.twig', [
            'post' => $post
        ]);
    }

    /**
     * @Route("/delete/{id}", name="delete")
     * @param $id
     * @param PostRepository $postRepository
     * @return Response
     */
    public function remove($id, PostRepository $postRepository){
        $em = $this->getDoctrine()->getManager();

        // esta linea y la signatura fue cambiada por mi porque no funciona si lo
        // hago como dice el video  hora 1:44:50
        $post = $postRepository->find($id);

        $em->remove($post);
        $em->flush();

        $this->addFlash('success', 'Post was remove');

        return $this->redirect($this->generateUrl('post.index'));
    }

}
