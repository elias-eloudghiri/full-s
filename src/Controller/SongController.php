<?php

namespace App\Controller;

use App\Entity\Song;
use App\Repository\PoolRepository;
use App\Repository\SongRepository;
use Doctrine\ORM\EntityManagerInterface;

use phpDocumentor\Reflection\Types\String_;
use Psr\Cache\InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

final class SongController extends AbstractController
{
    /**
     * @throws InvalidArgumentException
     */
    #[Route('api/v1/song', name: 'api_get_all_song', methods: ['GET'])]
    public function getAll(SongRepository $songRepository, TagAwareCacheInterface $cache, SerializerInterface $serializer): JsonResponse
    {
        $songs = $songRepository->findAll();
        $idCache = 'getAllSongs';
        $jsonData = $cache->get($idCache, function (ItemInterface $item) use ($songRepository, $serializer) {
            $item->tag('getAllSongs');
            $data = $songRepository->findAll();
            return $serializer->serialize($data, 'json', ['groups' => ['song', 'stats']]);
        });
        //$jsonData = $serializer->serialize($songs, 'json', ['groups' => ['song', 'stats']]);

        return new JsonResponse(data: $jsonData, status: Response::HTTP_OK, headers: [], json: true);
    }

    #[Route('api/v1/song/{id}', name: 'api_get_song', methods: ['GET'])]
    public function get(Song $id, SongRepository $songRepository, SerializerInterface $serializer): JsonResponse
    {
        $jsonData = $serializer->serialize($id, 'json', ['groups' => ['song', 'stats']]);

        return new JsonResponse(data: $jsonData, status: Response::HTTP_OK, headers: [], json: true);
    }

    /**
     * @throws InvalidArgumentException
     */
    #[Route('api/v1/song', name: 'api_create_song', methods: ['POST'])]
    public function create(ValidatorInterface     $validator,
                           Request                $request,
                           PoolRepository         $poolRepository,
                           UrlGeneratorInterface  $urlGenerator,
                           SerializerInterface    $serializer,
                           EntityManagerInterface $entityManager,
                           TagAwareCacheInterface $cache): JsonResponse
    {
        $song = $serializer->deserialize($request->getContent(), Song::class, 'json');
        $idPool = $request->toArray()['idPool'] ?? null;
        $pool = $poolRepository->find($idPool);
        $song->addPool($pool);
        $song->setName($song->getName() ?? 'Non défini');
        $song->setStatus('on');
        $errors = $validator->validate($song);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), Response::HTTP_BAD_REQUEST, [], true);
        }
        $entityManager->persist($song);
        $entityManager->flush();
        $cache->invalidateTags(["getAllSongs"]);
        $jsonData = $serializer->serialize($song, 'json', ['groups' => ['song', 'stats']]);
        $location = $urlGenerator->generate('get_song', ['id' => $song->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse(data: $jsonData, status: Response::HTTP_CREATED, headers: ["Location" => $location], json: true);
    }

    /**
     * @throws InvalidArgumentException
     */
    #[Route('api/v1/song/{id}', name: 'api_update_song', methods: ['PUT'])]
    public function update(Song                   $id,
                           Request                $request,
                           UrlGeneratorInterface  $urlGenerator,
                           SerializerInterface    $serializer,
                           EntityManagerInterface $entityManager,
                           TagAwareCacheInterface $cache): JsonResponse
    {
        $newSong = $serializer->deserialize($request->getContent(), Song::class, 'json', context: [AbstractNormalizer::OBJECT_TO_POPULATE => $id]);
        $entityManager->persist($newSong);
        $entityManager->flush();
        $cache->invalidateTags(["getAllSongs"]);

        return new JsonResponse(data: null, status: Response::HTTP_NO_CONTENT);
    }

    #[Route('api/v1/song/{id}', name: 'api_delete_song', methods: ['DELETE'])]
    public function delete(Song $id, Request $request, UrlGeneratorInterface $urlGenerator, SerializerInterface $serializer, EntityManagerInterface $entityManager): JsonResponse
    {
        if ('' !== $request->getContent() && true === $request->toArray()['delete']) {
            $entityManager->remove($id);
        } else {
            $id->setStatus('off');
            $entityManager->persist($id);
        }

        $entityManager->flush();

        return new JsonResponse(data: null, status: Response::HTTP_NO_CONTENT);
    }
}
