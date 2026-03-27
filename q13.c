#include <stdio.h>

int main () {

    float tC, tF;
    puts("Digite a temperatura (em °C):");   
    scanf("%f", &tC);

    tF = (tC * 9/5) + 32;
    printf("A temperatura em Fahrenheit é: %.2f F.\n", tF);

    return 0;
}