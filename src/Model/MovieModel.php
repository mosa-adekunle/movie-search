<?php

namespace App\Model;

use Symfony\Component\HttpFoundation\Request;


/**
 * Class MovieModel
 * @package App\Model
 */
class MovieModel
{
    private $movie_file_name;

    public function __construct(){
        $this->movie_file_name = "../public/csvs/movies.csv";
    }

    /**
     * Pulls a list of movies from a csv file
     * Finds the movie info
     * Saves the result of the movie info search
     * @param $movie_name
     * @return iterable|null
     */
    public function findMovieInfo($movie_name): ?iterable
    {
        $movies_list = $this->getCSVFileAsArray();

        if ($movies_list) {
            $movie = $this->findMovie($movie_name, $movies_list);

            if ($movie) {
                $movie_info = $this->getMovieInfo($movie);
                $this->saveMovieInfo($movie, $movie_info);
                return $movie_info;
            }
        }
        return null;
    }


    /**
     * Returns the data in a json save file
     * @param $json_save_file
     * @return iterable
     */
    public function getSavedMovieInfo($json_save_file): ?iterable
    {
        $saved_movie_info = [];

        if (file_exists($json_save_file)) {
            $saved_movie_info = $this->readFile($json_save_file);
            return json_decode($saved_movie_info, true);
        }

        return $saved_movie_info;
    }


    /**
     * Returns detailed information about a movie
     * Checks the local store if information is there, if not checks an API
     * @param $movie
     * @return mixed|null
     */
    public function getMovieInfo($movie)
    {
        $json_save_file = "../public/storage/storage.json";
        $saved_movie_info = $this->getSavedMovieInfo($json_save_file);
        $movie_id = $movie["id"];

        if (isset($saved_movie_info[$movie_id])) {
            return $saved_movie_info[$movie_id];
        } else {
            $movie_info = Curler::getCurlResult("http://www.omdbapi.com/?i=$movie_id&apikey=b1b5d454");
            return $movie_info;
        }
    }


    /**
     * Saves movie info to a json file
     * @param $movie
     * @param $movie_info
     */
    public function saveMovieInfo($movie, $movie_info)
    {
        $json_save_file = "../public/storage/storage.json";
        $movie_info_array = $this->getSavedMovieInfo($json_save_file);

        if (!isset($movie_info_array[$movie["id"]])) {
            $movie_info_array[$movie["id"]] = $movie_info;
        }

        $movie_info_json = json_encode($movie_info_array);
        $this->writeFile($json_save_file, $movie_info_json);

    }


    /**
     * Reads a local file with a matching file name
     * @param $file_name
     * @return false|string
     */
    public function readFile($file_name)
    {
        try {
            $file = fopen($file_name, "r") or die("Unable to open file!");
            if(filesize($file_name)>0){
                $file_data = fread($file, filesize($file_name));
                fclose($file);
                return $file_data;
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }


    /**
     * Writes to a file
     * @param $file
     * @param $content
     */
    public function writeFile($file, $content)
    {
        try {
            file_put_contents($file, $content);
        } catch (Exception $e) {
            echo $e->getMessage();
        }

    }


    /**
     * Returns a movie with a matching title name
     * @param $movie_name
     * @param $movie_list
     * @return iterable|null
     */
    public function findMovie($movie_name, $movie_list) :?iterable
    {
        foreach ($movie_list as $movie) {
            if (strtolower($movie["title"]) == strtolower($movie_name)) {
                return $movie;
            }
        }
        return null;
    }


    /**
     * Returns the contents of a csv file as an array
     * @return array
     */
    public function getCSVFileAsArray(): array
    {

        $csv = array_map("str_getcsv", file($this->movie_file_name, FILE_SKIP_EMPTY_LINES));
        $keys = array_shift($csv);
        $sorted_movies = [];

        foreach ($csv as $i => $row) {
            $sorted_movies[$row[0]] = array_combine($keys, $row);
        }
        return $sorted_movies;

    }


}