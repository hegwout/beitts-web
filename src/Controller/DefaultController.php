<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Google\Cloud\TextToSpeech\V1\AudioConfig;
use Google\Cloud\TextToSpeech\V1\AudioEncoding;
use Google\Cloud\TextToSpeech\V1\SsmlVoiceGender;
use Google\Cloud\TextToSpeech\V1\SynthesisInput;
use Google\Cloud\TextToSpeech\V1\TextToSpeechClient;
use Google\Cloud\TextToSpeech\V1\VoiceSelectionParams;
use Google\Cloud\Storage\StorageClient;

use Psr\Log\LoggerInterface;



class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="default")
     */
    public function index()
    {
        return $this->json([
            'message' => 'ok' 
        ]);
    }

    /**
     * @Route("/ok", name="default")
     */
    public function indexOk( )
    {
        header("Access-Control-Allow-Origin: *");
        //$response->headers->set('Content-Type', 'application/json');
        // Allow all websites
        //$response->headers->set('Access-Control-Allow-Origin', '*');
        return $this->json(['message'=>"ok"]);
    }

    /**
     * @Route("/tts", name="default_tts")
     */
    public function tts(Request $request,LoggerInterface $logger)
    {
        
        header("Access-Control-Allow-Origin: *");
        $logger->info("data:" . $request->getContent());
        $data = json_decode($request->getContent(),true);

        
        $text = isset($data['text']) ? $data['text'] : null;
        $people = isset($data['people']) ? $data['people'] : null;
        $speed = isset($data['speed']) ? $data['speed'] : 1;
        $speed = str_replace("X", "", $speed);
        
        $logger->info("text: $text");
        $logger->info("people: $people");
        
        $client = new TextToSpeechClient(
           ['credentials' => __DIR__ . '/../../config/project-a44717265544.json']
        ); 
        // sets text to be synthesised
        $synthesisInputText = (new SynthesisInput())
            ->setText($text);

        // build the voice request, select the language code ("en-US") and the ssml
        // voice gender
        $voice = (new VoiceSelectionParams())
            ->setLanguageCode('en-US')
            ->setSsmlGender(SsmlVoiceGender::MALE);

        // Effects profile
        $effectsProfileId = "telephony-class-application";

        // select the type of audio file you want returned
        $audioConfig = (new AudioConfig())
            ->setAudioEncoding(AudioEncoding::MP3)
            ->setSpeakingRate($speed)
            ->setEffectsProfileId(array($effectsProfileId));

        // perform text-to-speech request on the text input with selected voice
        // parameters and audio file type
        $response = $client->synthesizeSpeech($synthesisInputText, $voice, $audioConfig);
        $audioContent = $response->getAudioContent();
        //self::create_budget();die("ok");
        // the response's audioContent is binary
        $path = 'data/mp3/' . date('Y-m-d-h')  . '/' ;

        if (!is_dir(__DIR__.'/../../public/' . $path)) {
          // dir doesn't exist, make it
          mkdir(__DIR__.'/../../public/' . $path);
        }
        $url = $path .  uniqid() .  '.mp3';
        file_put_contents(__DIR__.'/../../public/' . $url , $audioContent);
 
        //echo __DIR__.'/../../public'.$url;
        // self::upload_object("pro-icon-253402-tts", $url , __DIR__.'/../../public/'.$url);
        // //echo 'Audio content written to "output.mp3"' . PHP_EOL;
        // unlink(__DIR__.'/../../public/'.$url);
        # [END tts_quickstart]
        // return $audioContent;

        return $this->json([
            'message' => 'ok' ,
            'url' => "http://tts.heoffice.com/" . $url
        ]);
    }
    function upload_object($bucketName, $objectName, $source)
    {
        // $storage = new StorageClient();
        $projectId = 'pro-icon-253402';

        $storage = new StorageClient([
            'projectId' => $projectId
        ]);
        
        # The name for the new bucket
        //$bucketName = 'my-new-bucket';
        
        # Creates the new bucket
        //$bucket = $storage->createBucket($bucketName);

        $file = fopen($source, 'r');
        $bucket = $storage->bucket($bucketName);
        $object = $bucket->upload($file, [
            'name' => $objectName
        ]);
        //printf('Uploaded %s to gs://%s/%s' . PHP_EOL, basename($source), $bucketName, $objectName);
    }

    function create_budget(){
        $projectId = 'pro-icon-253402';

        # Instantiates a client
        $storage = new StorageClient([
            'projectId' => $projectId
        ]);

        # The name for the new bucket
        $bucketName = 'pro-icon-253402-tts';

        # Creates the new bucket
        $bucket = $storage->createBucket($bucketName);

        echo 'Bucket ' . $bucket->name() . ' created.';
    }
    
}
