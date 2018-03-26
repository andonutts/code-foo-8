package andypasti.chickenroad;

import java.util.Scanner;
import java.util.List;
import java.util.ArrayList;
import java.util.regex.Matcher;
import java.util.regex.Pattern;
import java.util.Random;

/**
 * Calculates valid paths for crossing a 'road' grid
 * 
 * Accepts and validates a user input grid consisting of Xs and Os, representing
 * traversable space and potholes (untraversable), respectively. A random, non-
 * pothole space on the left is then selected and all valid paths to the right-
 * hand side from that space are calculated and printed.
 * 
 * @author Andy He
 *
 */
public class ChickenRoad {

    public static void main(String[] args) {
        System.out.print("Input the road grid below, row by row. The letter 'O' represents a traversable space,\n"
                + "while the letter 'X' represents a pothole. Use a semicolon to denote the end of the input.\n"
                + "Input is case-insensitive, and all whitespace will be ignored.\n");
        
        Scanner scan = new Scanner(System.in);
        
        List<char[]> gridList = new ArrayList<char[]>();
        boolean endOfInput = false;
        int width = 0;
        
        // scan for user input
        while(!endOfInput) {
            
            String newRow = scan.nextLine();
            
            // if a semicolon is detected, ignore all inputs following it and signal end of input
            if(newRow.contains(";")) {
                newRow = newRow.substring(0, newRow.indexOf(";"));
                endOfInput = true;
            }
            
            // ignore empty rows
            if(!newRow.isEmpty()) {
                
                // check if any invalid characters are present
                Pattern inputRegex = Pattern.compile("[oxOX ]+");
                Matcher matcher = inputRegex.matcher(newRow);
                if(!matcher.matches()) {
                    System.out.println("ERROR: Invalid character");
                    System.exit(1);
                }
                
                // remove all whitespace and convert remaining characters to uppercase
                newRow = newRow.replaceAll("\\s+","");
                newRow = newRow.toUpperCase();
                char rowElements[] = newRow.toCharArray();
                
                // make sure the row width is consistent; the first row entry determines the width
                if(gridList.size() == 0) {
                    width = rowElements.length;
                } else {
                    int currentWidth = rowElements.length;
                    if(currentWidth != width) {
                        System.out.println("ERROR: Inconsistent row width");
                        System.exit(1);
                    }
                }
                
                // add the row to the list
                gridList.add(rowElements);
            }
        }
        scan.close();
        System.out.println();
        
        // check if the grid is empty
        if(gridList.size() == 0) {
            System.out.println("ERROR: Empty grid");
            System.exit(1);
        };
        
        int height = gridList.size();
        
        // translate the input grid so that the array indices match the coordinate system
        char grid[][] = new char[width][height];
        for(int i = 0; i <= height-1; i++) {
            for(int j = 0; j <= width-1; j++) {
                grid[j][height-1-i] = gridList.get(i)[j];
            }
        }
        
        // choose a valid (non-pothole) random y-coordinate to start from
        List<Integer> yStartPts = new ArrayList<Integer>();
        for(int i = 0; i <= height-1; i++) {
            if(grid[0][i] == 'O') {
                yStartPts.add(i);
            }
        }
        
        // exit if there are no valid starting points
        if(yStartPts.size() == 0) {
            System.out.println("ERROR: No valid starting points");
            System.exit(1);
        }
        Random rng = new Random();
        int yStart = yStartPts.get(rng.nextInt(yStartPts.size()));
        int xStart = 0;
        
        // print the road grid
        System.out.println("Road grid:");
        for(int j = grid[0].length-1; j >= 0; j--) {
            for(int i = 0; i <= grid.length-1; i++) {
                System.out.print(grid[i][j] + " ");
            }
            System.out.println();
        }
        System.out.println();
        
        // recursively search for and print valid paths
        System.out.println("Valid paths when starting at (" + xStart + ", " + yStart + "):");
        String pathStr = "";
        int count[] = {0};
        checkTile(xStart, yStart, grid, pathStr, count);
        System.out.println();
        
        // print number of valid paths
        System.out.println("Total valid paths from starting point (" + xStart + ", " + yStart + ") is " + count[0]);
        
        return;
    }
    
    public static void checkTile(int x, int y, char[][] road, String path, int[] pathCount) {
        // check if tile is outside road or if tile is untraversable; if true, return
        if(x < 0 || y < 0 || x > (road.length-1) || y > (road[0].length-1) || road[x][y] == 'X') {
            return;
        }
        
        // concatenate the new coordinates to the path string
        path += "(" + x + ", " + y + ")";
        
        // check if destination has been reached; if true, print path string, increment count, and return
        if(x == road.length - 1) {
            System.out.println(path);
            pathCount[0]++;
            return;
        }
        
        // mark current coordinates as untraversable to prevent backtracking
        road[x][y] = 'X';
        
        // recursively check adjacent tiles
        path += " -> ";
        checkTile(x, y + 1, road, path, pathCount);
        checkTile(x + 1, y, road, path, pathCount);
        checkTile(x, y - 1, road, path, pathCount);
        
        // re-mark current coordinates as traversable for future path calculations
        road[x][y] = 'O';
        
        return;
    }
}
