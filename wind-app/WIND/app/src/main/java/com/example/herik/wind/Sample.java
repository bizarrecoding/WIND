package com.example.herik.wind;

/**
 * Created by Herik on 6/5/2017.
 */

public class Sample {
    private String speed;
    private String direction;
    private String time;

    public Sample(String speed, String direction, String time){
        this.speed=speed;
        this.direction=direction;
        this.time=time;
    }

    public String getSpeed() {
        return speed;
    }

    public void setSpeed(String speed) {
        this.speed = speed;
    }

    public String getDirection() {
        return direction;
    }

    public void setDirection(String direction) {
        this.direction = direction;
    }

    public String getTime() {
        return time;
    }

    public void setTime(String time) {
        this.time = time;
    }
}
